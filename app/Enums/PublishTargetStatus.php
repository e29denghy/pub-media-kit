<?php

namespace App\Enums;

enum PublishTargetStatus: string
{
    case Pending = 'pending';
    case PlanReady = 'plan_ready';
    case AwaitingConfirmation = 'awaiting_confirmation';
    case Confirmed = 'confirmed';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function labelZh(): string
    {
        return match ($this) {
            self::Pending => '待处理',
            self::PlanReady => '计划已生成',
            self::AwaitingConfirmation => '待人工确认',
            self::Confirmed => '已人工确认',
            self::Failed => '失败',
            self::Skipped => '已跳过',
        };
    }
}
