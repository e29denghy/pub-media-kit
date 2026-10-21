<?php

namespace App\Models;

use App\Enums\PublishJobStatus;
use App\Enums\PublishTargetStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'draft_id', 'publish_combination_id', 'platforms', 'status'])]
class PublishJob extends Model
{
    protected $appends = ['status_label'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platforms' => 'array',
            'status' => PublishJobStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function combination(): BelongsTo
    {
        return $this->belongsTo(PublishCombination::class, 'publish_combination_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PublishTarget::class);
    }

    public function refreshStatus(): void
    {
        $this->load('targets');

        $statuses = $this->targets->map(fn (PublishTarget $target) => $target->status);

        $awaiting = $statuses->contains(fn (PublishTargetStatus $status) => in_array($status, [
            PublishTargetStatus::Pending,
            PublishTargetStatus::AwaitingConfirmation,
        ], true));

        $terminal = [
            PublishTargetStatus::Confirmed,
            PublishTargetStatus::PlanReady,
            PublishTargetStatus::Skipped,
        ];

        if ($statuses->contains(PublishTargetStatus::Failed) && ! $awaiting) {
            $this->status = PublishJobStatus::Failed;
        } elseif ($awaiting && $statuses->contains(PublishTargetStatus::Confirmed)) {
            $this->status = PublishJobStatus::PartiallyConfirmed;
        } elseif ($awaiting) {
            $this->status = PublishJobStatus::AwaitingConfirmation;
        } elseif ($statuses->isNotEmpty() && $statuses->every(fn (PublishTargetStatus $status) => in_array($status, $terminal, true))) {
            $this->status = PublishJobStatus::Completed;
        }

        $this->save();
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => $this->status?->labelZh());
    }
}
