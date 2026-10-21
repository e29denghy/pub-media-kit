<?php

namespace App\Enums;

enum PublishMode: string
{
    case BrowserSessionDraft = 'browser_session_draft';
    case OfficialApi = 'official_api';

    public function label(): string
    {
        return match ($this) {
            self::BrowserSessionDraft => 'Browser session, fill draft',
            self::OfficialApi => 'Official API',
        };
    }

    public function labelZh(): string
    {
        return match ($this) {
            self::BrowserSessionDraft => '网页会话填草稿',
            self::OfficialApi => '官方 API',
        };
    }
}
