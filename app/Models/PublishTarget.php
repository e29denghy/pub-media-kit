<?php

namespace App\Models;

use App\Enums\Platform;
use App\Enums\PublishMode;
use App\Enums\PublishTargetStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['publish_job_id', 'platform', 'mode', 'status', 'summary', 'plan', 'confirmed_at'])]
class PublishTarget extends Model
{
    protected $appends = [
        'status_label',
        'mode_label',
        'platform_label',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'mode' => PublishMode::class,
            'status' => PublishTargetStatus::class,
            'plan' => 'array',
            'confirmed_at' => 'datetime',
        ];
    }

    public function publishJob(): BelongsTo
    {
        return $this->belongsTo(PublishJob::class);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => $this->status?->labelZh());
    }

    protected function modeLabel(): Attribute
    {
        return Attribute::get(fn () => $this->mode?->labelZh());
    }

    protected function platformLabel(): Attribute
    {
        return Attribute::get(fn () => $this->platform?->labelZh());
    }
}
