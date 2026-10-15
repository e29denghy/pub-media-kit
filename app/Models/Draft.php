<?php

namespace App\Models;

use App\Enums\DraftStatus;
use Database\Factories\DraftFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'body', 'body_format', 'tags', 'status'])]
class Draft extends Model
{
    /** @use HasFactory<DraftFactory> */
    use HasFactory;

    protected $appends = ['status_label'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'status' => DraftStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    public function publishJobs(): HasMany
    {
        return $this->hasMany(PublishJob::class);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => $this->status?->labelZh());
    }
}
