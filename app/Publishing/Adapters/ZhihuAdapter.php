<?php

namespace App\Publishing\Adapters;

use App\Enums\Platform;

/**
 * 知乎网页编辑器填草稿。
 *
 * 普通创作者没有稳定的官方发布 API。阶段一与公众号相同：填进编辑器，人点击发布。
 */
class ZhihuAdapter extends BrowserSessionDraftAdapter
{
    public function description(): string
    {
        return '知乎专栏网页编辑器填写标题、正文和话题，人确认后才发布。';
    }

    protected function platform(): Platform
    {
        return Platform::Zhihu;
    }

    protected function adminOrigin(): string
    {
        return (string) config('platforms.zhihu.admin_origin');
    }

    protected function editorHint(): string
    {
        return (string) config('platforms.zhihu.editor_hint');
    }

    protected function summary(): string
    {
        return '已生成知乎网页草稿填写计划。不会请求知乎接口，也不会点击发布。';
    }

    protected function summaryEn(): string
    {
        return 'Browser-session fill plan for a Zhihu draft. No Zhihu API request and no publish click.';
    }

    protected function warnings(): array
    {
        return [
            '阶段一不接知乎官方发布接口。发布动作留在网页编辑器里由人完成。',
        ];
    }

    protected function warningsEn(): array
    {
        return [
            'Phase 1 does not call a Zhihu official publish API. Publishing stays in the web editor for a person to finish.',
        ];
    }

    protected function bodyNote(): string
    {
        return '写入正文。Markdown 与知乎编辑器格式的转换留给 runner。';
    }

    protected function bodyNoteEn(): string
    {
        return 'Write the body. Mapping Markdown onto the Zhihu editor is left to a runner.';
    }

    protected function tagsNote(): string
    {
        return '标签对应知乎话题。编辑器里逐个添加；找不到的话题跳过并留给人处理。';
    }

    protected function tagsNoteEn(): string
    {
        return 'Tags map to Zhihu topics. Add them in the editor; leave unknown topics for a person.';
    }
}
