<?php

namespace App\Enums;

enum Platform: string
{
    case WechatOfficialAccount = 'wechat_oa';
    case Zhihu = 'zhihu';
    case X = 'x';

    public function label(): string
    {
        return match ($this) {
            self::WechatOfficialAccount => 'WeChat Official Account',
            self::Zhihu => 'Zhihu',
            self::X => 'X',
        };
    }

    public function labelZh(): string
    {
        return match ($this) {
            self::WechatOfficialAccount => '微信公众号',
            self::Zhihu => '知乎',
            self::X => 'X',
        };
    }
}
