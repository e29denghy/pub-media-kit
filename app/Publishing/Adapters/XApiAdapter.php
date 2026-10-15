<?php

namespace App\Publishing\Adapters;

use App\Enums\Platform;
use App\Enums\PublishMode;
use App\Models\Draft;
use App\Publishing\DraftFillPlan;
use App\Publishing\FillAction;
use App\Publishing\FillStep;
use App\Publishing\PlatformAdapter;

/**
 * X official API placeholder.
 *
 * The intended call is POST {base}/2/tweets with a JSON text body. Phase 1
 * never sends it, even when X_API_BEARER_TOKEN is present. The token is not
 * copied into the plan.
 */
class XApiAdapter implements PlatformAdapter
{
    public function key(): string
    {
        return Platform::X->value;
    }

    public function label(): string
    {
        return Platform::X->label();
    }

    public function labelZh(): string
    {
        return Platform::X->labelZh();
    }

    public function description(): string
    {
        return '官方 API 占位。阶段一只生成 tweets 载荷，不发任何请求。';
    }

    public function mode(): PublishMode
    {
        return PublishMode::OfficialApi;
    }

    public function phase(): int
    {
        return 0;
    }

    public function publishesAutomatically(): bool
    {
        return false;
    }

    public function prepareDraftFill(Draft $draft, array $options = []): DraftFillPlan
    {
        $base = rtrim((string) config('platforms.x.base_url'), '/');
        $path = (string) config('platforms.x.tweets_path');
        $text = $this->intendedText($draft);
        $credentialsPresent = filled(config('platforms.x.bearer_token'));

        return new DraftFillPlan(
            platform: $this->key(),
            mode: $this->mode(),
            outcome: DraftFillPlan::STUB,
            summary: 'X 官方 API 仍是占位。没有发送请求，也没有发表。',
            summaryEn: 'The X official API line is still a stub. No request was sent and nothing was published.',
            steps: [
                new FillStep(
                    order: 1,
                    action: FillAction::BUILD_PAYLOAD,
                    field: 'text',
                    value: $text,
                    note: '预定载荷为 POST /2/tweets 的 JSON：{"text": "..."}。超长正文会截断到 280 字。此值只保存在本系统。',
                    noteEn: 'Intended payload is POST /2/tweets JSON: {"text": "..."}. Long copy is truncated to 280 characters. The value stays in this app.',
                ),
                new FillStep(
                    order: 2,
                    action: FillAction::SKIP_NETWORK,
                    field: 'publish',
                    value: null,
                    note: '阶段一故意不调用 api.x.com。配置了 token 也不会发送。',
                    noteEn: 'Phase 1 deliberately does not call api.x.com. A configured token is still not sent.',
                ),
            ],
            requiresHumanConfirm: false,
            warnings: [
                '这不是可用的发布线路。后续阶段才会接上官方 API 客户端。',
            ],
            warningsEn: [
                'This is not a working publish line. A later phase can attach an official API client.',
            ],
            meta: [
                'endpoint' => $base.$path,
                'method' => 'POST',
                'payload_shape' => [
                    'text' => $text,
                ],
                'credentials_present' => $credentialsPresent,
                'sent' => false,
            ],
            networkCalled: false,
        );
    }

    private function intendedText(Draft $draft): string
    {
        $text = trim($draft->title."\n\n".$draft->body);

        if (mb_strlen($text) <= 280) {
            return $text;
        }

        return mb_substr($text, 0, 277).'...';
    }
}
