<?php

namespace Tests\Unit;

use App\Models\Draft;
use App\Publishing\Adapters\WechatOfficialAccountAdapter;
use App\Publishing\Adapters\XApiAdapter;
use App\Publishing\Adapters\ZhihuAdapter;
use App\Publishing\DraftFillPlan;
use App\Publishing\FillAction;
use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class PlatformAdapterTest extends TestCase
{
    public function test_registry_exposes_the_three_lines_in_order(): void
    {
        $registry = $this->app->make(PlatformAdapterRegistry::class);

        $this->assertSame(['wechat_oa', 'zhihu', 'x'], $registry->keys());
        $this->assertCount(2, $registry->phaseOne());
        $this->assertSame(['wechat_oa', 'zhihu'], array_map(
            fn ($adapter) => $adapter->key(),
            $registry->phaseOne(),
        ));

        foreach ($registry->all() as $adapter) {
            $this->assertFalse($adapter->publishesAutomatically());
        }
    }

    public function test_unknown_platform_is_rejected(): void
    {
        $registry = $this->app->make(PlatformAdapterRegistry::class);

        $this->expectException(InvalidArgumentException::class);

        $registry->get('douyin');
    }

    public function test_wechat_and_zhihu_plans_stop_before_publish_and_do_not_call_the_network(): void
    {
        Http::preventStrayRequests();

        $draft = $this->draft();

        foreach ([new WechatOfficialAccountAdapter, new ZhihuAdapter] as $adapter) {
            $plan = $adapter->prepareDraftFill($draft);

            $this->assertSame(DraftFillPlan::READY, $plan->outcome);
            $this->assertTrue($plan->requiresHumanConfirm);
            $this->assertFalse($plan->networkCalled);
            $this->assertSame(
                FillAction::AWAIT_HUMAN_PUBLISH,
                $plan->steps[array_key_last($plan->steps)]->action,
            );
        }

        Http::assertNothingSent();
    }

    public function test_wechat_plan_documents_why_personal_accounts_skip_the_official_api(): void
    {
        $plan = (new WechatOfficialAccountAdapter)->prepareDraftFill($this->draft());
        $warnings = implode(' ', $plan->warnings).' '.implode(' ', $plan->warningsEn);

        $this->assertStringContainsString('未认证', $warnings);
        $this->assertStringContainsString('official draft/publish API', $warnings);
    }

    public function test_x_stub_never_sends_and_never_copies_the_token_into_the_plan(): void
    {
        Http::preventStrayRequests();
        config(['platforms.x.bearer_token' => 'super-secret-token']);

        $plan = (new XApiAdapter)->prepareDraftFill($this->draft());

        $this->assertSame(DraftFillPlan::STUB, $plan->outcome);
        $this->assertFalse($plan->requiresHumanConfirm);
        $this->assertFalse($plan->networkCalled);
        $this->assertFalse($plan->meta['sent']);
        $this->assertTrue($plan->meta['credentials_present']);
        $this->assertSame(FillAction::SKIP_NETWORK, $plan->steps[1]->action);
        $this->assertStringNotContainsString('super-secret-token', json_encode($plan->toArray()));

        Http::assertNothingSent();
    }

    private function draft(): Draft
    {
        $draft = new Draft([
            'title' => '标题',
            'body' => '正文一段',
            'tags' => ['话题'],
        ]);
        $draft->setRelation('mediaAssets', collect());

        return $draft;
    }
}
