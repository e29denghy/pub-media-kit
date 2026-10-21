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
 * Shared flow for lines that fill a logged-in web admin and then stop.
 *
 * Subclasses only supply copy and the operator-facing entry hint. They do
 * not receive cookies, passwords, or a browser. A later runner maps
 * FillAction values onto Playwright or an extension.
 */
abstract class BrowserSessionDraftAdapter implements PlatformAdapter
{
    abstract protected function platform(): Platform;

    abstract protected function adminOrigin(): string;

    abstract protected function editorHint(): string;

    abstract protected function summary(): string;

    abstract protected function summaryEn(): string;

    /**
     * @return list<string>
     */
    abstract protected function warnings(): array;

    /**
     * @return list<string>
     */
    abstract protected function warningsEn(): array;

    abstract protected function bodyNote(): string;

    abstract protected function bodyNoteEn(): string;

    abstract protected function tagsNote(): string;

    abstract protected function tagsNoteEn(): string;

    public function key(): string
    {
        return $this->platform()->value;
    }

    public function label(): string
    {
        return $this->platform()->label();
    }

    public function labelZh(): string
    {
        return $this->platform()->labelZh();
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
        $draft->loadMissing('mediaAssets');

        $origin = rtrim($this->adminOrigin(), '/');
        $mediaNames = $draft->mediaAssets
            ->pluck('original_name')
            ->filter()
            ->implode(', ');

        $tags = implode(', ', $draft->tags ?? []);

        return new DraftFillPlan(
            platform: $this->key(),
            mode: $this->mode(),
            outcome: DraftFillPlan::READY,
            summary: $this->summary(),
            summaryEn: $this->summaryEn(),
            steps: [
                new FillStep(
                    order: 1,
                    action: FillAction::ENSURE_SESSION,
                    field: 'session',
                    value: $origin,
                    note: '使用操作者自己已经登录的网页会话（Playwright 持久化配置或浏览器扩展）。本适配器不采集密码，也不导出 Cookie。',
                    noteEn: 'Use the operator’s own already-logged-in browser session (Playwright persistent context or an extension). This adapter does not collect passwords or export cookies.',
                ),
                new FillStep(
                    order: 2,
                    action: FillAction::OPEN_EDITOR,
                    field: 'editor',
                    value: $origin,
                    note: '打开网页后台并进入编辑器。入口：'.$this->editorHint().'。选择器留给 runner，这里不写死。',
                    noteEn: 'Open the web admin editor. Entry: '.$this->editorHint().'. Selectors belong to a future runner and are intentionally not hardcoded.',
                ),
                new FillStep(
                    order: 3,
                    action: FillAction::FILL_FIELD,
                    field: 'title',
                    value: $draft->title,
                    note: '把草稿标题写入编辑器标题。',
                    noteEn: 'Write the draft title into the editor title field.',
                ),
                new FillStep(
                    order: 4,
                    action: FillAction::FILL_FIELD,
                    field: 'body',
                    value: $draft->body,
                    note: $this->bodyNote(),
                    noteEn: $this->bodyNoteEn(),
                ),
                new FillStep(
                    order: 5,
                    action: FillAction::FILL_FIELD,
                    field: 'tags',
                    value: $tags !== '' ? $tags : null,
                    note: $this->tagsNote(),
                    noteEn: $this->tagsNoteEn(),
                ),
                new FillStep(
                    order: 6,
                    action: FillAction::ATTACH_MEDIA,
                    field: 'media',
                    value: $mediaNames !== '' ? $mediaNames : null,
                    note: '把本地素材交给 runner 上传。阶段一只记录文件名，不实际上传。',
                    noteEn: 'Hand local assets to a runner for upload. Phase 1 records filenames only and does not upload.',
                ),
                new FillStep(
                    order: 7,
                    action: FillAction::AWAIT_HUMAN_PUBLISH,
                    field: 'publish',
                    value: null,
                    note: '停在草稿。由人在网页后台检查后自行点击发表。本系统只在人确认后更新任务状态。',
                    noteEn: 'Stop on the draft. A person reviews it in the web admin and clicks publish. This app only updates job status after that confirmation.',
                ),
            ],
            requiresHumanConfirm: true,
            warnings: $this->warnings(),
            warningsEn: $this->warningsEn(),
            meta: [
                'admin_origin' => $origin,
                'editor_hint' => $this->editorHint(),
                'runner' => 'unconfigured',
            ],
            networkCalled: false,
        );
    }
}
