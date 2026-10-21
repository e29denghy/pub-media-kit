<?php

namespace App\Enums;

enum PublishJobStatus: string
{
    case Queued = 'queued';
    case AwaitingConfirmation = 'awaiting_confirmation';
    case PartiallyConfirmed = 'partially_confirmed';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function labelZh(): string
    {
        return match ($this) {
            self::Queued => '排队中',
            self::AwaitingConfirmation => '待人工确认',
            self::PartiallyConfirmed => '部分已确认',
            self::Completed => '已完成',
            self::Failed => '失败',
            self::Cancelled => '已取消',
        };
    }
}
