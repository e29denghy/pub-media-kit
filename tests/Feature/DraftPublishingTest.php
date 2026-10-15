<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Enums\PublishJobStatus;
use App\Enums\PublishMode;
use App\Enums\PublishTargetStatus;
use App\Models\Draft;
use App\Models\PublishJob;
use App\Models\User;
use App\Publishing\DraftFillPlan;
use App\Publishing\PlatformAdapter;
use App\Publishing\PlatformAdapterRegistry;
use App\Publishing\PublishJobPlanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class DraftPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('drafts.index'))->assertRedirect(route('login'));
    }

    public function test_dashboard_lists_the_three_adapters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('adapters', 3)
                ->where('adapters.0.key', 'wechat_oa')
                ->where('adapters.1.key', 'zhihu')
                ->where('adapters.2.key', 'x'));
    }

    public function test_dashboard_renders_when_jobs_exist(): void
    {
        $user = User::factory()->create();
        $draft = Draft::factory()->for($user)->create();
        app(PublishJobPlanner::class)->plan($user, $draft, ['wechat_oa', 'zhihu']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('jobs', 1)
                ->where('jobs.0.status', 'awaiting_confirmation'));
    }

    public function test_creating_a_draft_queues_wechat_and_zhihu_for_human_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('drafts.store'), [
                'title' => '双发标题',
                'body' => '这是正文',
                'tags' => '公众号，知乎',
                'platforms' => ['wechat_oa', 'zhihu'],
                'queue' => true,
            ])
            ->assertRedirect();

        $draft = Draft::query()->firstOrFail();
        $this->assertSame(['公众号', '知乎'], $draft->tags);

        $job = $draft->publishJobs()->with('targets')->firstOrFail();
        $this->assertSame(PublishJobStatus::AwaitingConfirmation, $job->status);
        $this->assertCount(2, $job->targets);

        foreach ($job->targets as $target) {
            $this->assertSame(PublishMode::BrowserSessionDraft, $target->mode);
            $this->assertSame(PublishTargetStatus::AwaitingConfirmation, $target->status);
            $this->assertFalse($target->plan['network_called']);
            $this->assertSame('await_human_publish', $target->plan['steps'][6]['action']);
        }

        $wechat = $job->targets->first(fn ($target) => $target->platform === Platform::WechatOfficialAccount);
        $this->actingAs($user)
            ->post(route('targets.confirm', $wechat))
            ->assertRedirect(route('jobs.show', $job));

        $this->assertSame(PublishTargetStatus::Confirmed, $wechat->fresh()->status);
        $this->assertSame(PublishJobStatus::PartiallyConfirmed, $job->fresh()->status);

        $zhihu = $job->targets->first(fn ($target) => $target->platform === Platform::Zhihu);
        $this->actingAs($user)->post(route('targets.confirm', $zhihu))->assertRedirect();

        $this->assertSame(PublishJobStatus::Completed, $job->fresh()->status);
    }

    public function test_x_only_job_is_a_completed_stub_and_cannot_be_confirmed_as_published(): void
    {
        $user = User::factory()->create();
        $draft = Draft::factory()->for($user)->create();

        $job = app(PublishJobPlanner::class)->plan($user, $draft, ['x']);

        $this->assertSame(PublishJobStatus::Completed, $job->status);
        $target = $job->targets->sole();
        $this->assertSame(PublishTargetStatus::PlanReady, $target->status);
        $this->assertSame(DraftFillPlan::STUB, $target->plan['outcome']);
        $this->assertFalse($target->plan['meta']['sent']);

        $this->actingAs($user)
            ->from(route('jobs.show', $job))
            ->post(route('targets.confirm', $target))
            ->assertSessionHasErrors('target');

        $this->assertSame(PublishTargetStatus::PlanReady, $target->fresh()->status);
    }

    public function test_a_combination_is_unioned_with_checked_platforms(): void
    {
        $user = User::factory()->create();
        $combination = $user->publishCombinations()->create([
            'name' => '图文双发',
            'platforms' => ['zhihu'],
            'notes' => null,
        ]);

        $this->actingAs($user)
            ->post(route('drafts.store'), [
                'title' => '组合',
                'body' => '正文',
                'platforms' => ['wechat_oa'],
                'publish_combination_id' => $combination->id,
                'queue' => true,
            ])
            ->assertRedirect();

        $platforms = PublishJob::query()->firstOrFail()->platforms;
        sort($platforms);

        $this->assertSame(['wechat_oa', 'zhihu'], $platforms);
    }

    public function test_queueing_without_platforms_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('drafts.create'))
            ->post(route('drafts.store'), [
                'title' => '只有正文',
                'body' => '正文',
                'platforms' => [],
                'queue' => true,
            ])
            ->assertSessionHasErrors('platforms');

        $this->assertDatabaseCount('drafts', 0);
    }

    public function test_job_from_the_draft_page_requires_a_platform(): void
    {
        $user = User::factory()->create();
        $draft = Draft::factory()->for($user)->create();

        $this->actingAs($user)
            ->from(route('drafts.show', $draft))
            ->post(route('drafts.jobs.store', $draft), [
                'platforms' => [],
            ])
            ->assertSessionHasErrors('platforms');

        $this->assertDatabaseCount('publish_jobs', 0);
    }

    public function test_other_users_cannot_open_a_draft(): void
    {
        $owner = User::factory()->create();
        $draft = Draft::factory()->for($owner)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('drafts.show', $draft))
            ->assertForbidden();
    }

    public function test_media_is_stored_on_the_local_disk(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('drafts.store'), [
                'title' => '带图',
                'body' => '正文',
                'queue' => false,
                'media' => [UploadedFile::fake()->image('cover.png')],
            ])
            ->assertRedirect();

        $asset = Draft::query()->firstOrFail()->mediaAssets()->firstOrFail();
        $this->assertSame('cover.png', $asset->original_name);
        $this->assertSame('local', $asset->disk);
        Storage::disk('local')->assertExists($asset->path);
    }

    public function test_planner_rejects_an_adapter_that_claims_to_have_called_the_network(): void
    {
        $user = User::factory()->create();
        $draft = Draft::factory()->for($user)->create();
        $planner = new PublishJobPlanner(new PlatformAdapterRegistry([
            new class implements PlatformAdapter
            {
                public function key(): string
                {
                    return 'wechat_oa';
                }

                public function label(): string
                {
                    return 'Bad';
                }

                public function labelZh(): string
                {
                    return '坏';
                }

                public function description(): string
                {
                    return '';
                }

                public function mode(): PublishMode
                {
                    return PublishMode::BrowserSessionDraft;
                }

                public function phase(): int
                {
                    return 1;
                }

                public function publishesAutomatically(): bool
                {
                    return false;
                }

                public function prepareDraftFill(Draft $draft, array $options = []): DraftFillPlan
                {
                    return new DraftFillPlan(
                        platform: 'wechat_oa',
                        mode: PublishMode::BrowserSessionDraft,
                        outcome: DraftFillPlan::READY,
                        summary: '',
                        summaryEn: '',
                        steps: [],
                        requiresHumanConfirm: true,
                        networkCalled: true,
                    );
                }
            },
        ]));

        $this->expectException(RuntimeException::class);

        $planner->plan($user, $draft, ['wechat_oa']);
    }
}
