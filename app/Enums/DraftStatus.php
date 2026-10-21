<?php

namespace App\Enums;

enum DraftStatus: string
{
    case Draft = 'draft';
    case Archived = 'archived';

    public function labelZh(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Archived => '已归档',
        };
    }
}
