<?php

namespace App\Publishing\Adapters;

use App\Enums\Platform;

/**
 * 微信公众号网页后台填草稿。
 *
 * 未认证的个人订阅号不能使用草稿箱 / 发布官方 API（cgi-bin/draft/* 需要已认证且开通接口权限的账号）。
 * 这条线路不伪装成官方接口：它只描述「已登录的 mp.weixin.qq.com 会话里填写草稿，人来点发表」。
 */
class WechatOfficialAccountAdapter extends BrowserSessionDraftAdapter
{
    public function description(): string
    {
        return '网页后台把标题和正文填进草稿，人确认后才发表。不调用个人未认证账号不可用的官方草稿 API。';
    }

    protected function platform(): Platform
    {
        return Platform::WechatOfficialAccount;
    }

    protected function adminOrigin(): string
    {
        return (string) config('platforms.wechat_oa.admin_origin');
    }

    protected function editorHint(): string
    {
        return (string) config('platforms.wechat_oa.editor_hint');
    }

    protected function summary(): string
    {
        return '已生成公众号网页草稿填写计划。不会调用官方接口，也不会点击发表。';
    }

    protected function summaryEn(): string
    {
        return 'Browser-session fill plan for a WeChat Official Account draft. No official API call and no publish click.';
    }

    protected function warnings(): array
    {
        return [
            '未认证的个人订阅号不能使用微信草稿/发布官方 API。此线路是网页会话填草稿，不是官方接口。',
        ];
    }

    protected function warningsEn(): array
    {
        return [
            'An unverified personal WeChat Official Account cannot use the official draft/publish API. This line fills the web admin draft. It is not that API.',
        ];
    }

    protected function bodyNote(): string
    {
        return '写入正文。Markdown 转公众号 HTML 由后续 runner 负责。';
    }

    protected function bodyNoteEn(): string
    {
        return 'Write the body. Converting Markdown to Official Account HTML is left to a future runner.';
    }

    protected function tagsNote(): string
    {
        return '若编辑器有摘要字段，则写入标签拼接成的摘要；没有就跳过。';
    }

    protected function tagsNoteEn(): string
    {
        return 'If the editor has a digest field, write the tags there. Otherwise skip.';
    }
}
