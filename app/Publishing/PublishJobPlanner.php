<?php

namespace App\Publishing;

use App\Enums\PublishJobStatus;
use App\Enums\PublishMode;
use App\Enums\PublishTargetStatus;
use App\Models\Draft;
use App\Models\PublishCombination;
use App\Models\PublishJob;
use App\Models\PublishTarget;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PublishJobPlanner
{
    public function __construct(private PlatformAdapterRegistry $adapters) {}

    /**
     * @param  list<string>  $platformKeys
     */
    public function plan(
        User $user,
        Draft $draft,
        array $platformKeys,
        ?PublishCombination $combination = null,
    ): PublishJob {
        $keys = $this->normalizeKeys($platformKeys, $combination);

        if ($keys === []) {
            throw ValidationException::withMessages([
                'platforms' => '至少选择一个平台，或选择一个平台组合。',
            ]);
        }

        return DB::transaction(function () use ($user, $draft, $combination, $keys) {
            $draft->loadMissing('mediaAssets');

            $job = PublishJob::query()->create([
                'user_id' => $user->id,
                'draft_id' => $draft->id,
                'publish_combination_id' => $combination?->id,
                'platforms' => $keys,
                'status' => PublishJobStatus::Queued,
            ]);

            foreach ($keys as $key) {
                $adapter = $this->adapters->get($key);
                $plan = $adapter->prepareDraftFill($draft);

                if ($plan->networkCalled || $adapter->publishesAutomatically()) {
                    throw new RuntimeException("Adapter [{$key}] must not publish or call the network while preparing a draft.");
                }

                $job->targets()->create([
                    'platform' => $adapter->key(),
                    'mode' => $adapter->mode(),
                    'status' => $plan->requiresHumanConfirm
                        ? PublishTargetStatus::AwaitingConfirmation
                        : PublishTargetStatus::PlanReady,
                    'summary' => $plan->summary,
                    'plan' => $plan->toArray(),
                ]);
            }

            $job->refreshStatus();

            return $job->load('targets');
        });
    }

    public function confirm(PublishTarget $target): void
    {
        if ($target->mode !== PublishMode::BrowserSessionDraft) {
            throw ValidationException::withMessages([
                'target' => '这条线路没有人工确认步骤。官方 API 占位不会被标记为已发表。',
            ]);
        }

        if ($target->status !== PublishTargetStatus::AwaitingConfirmation) {
            throw ValidationException::withMessages([
                'target' => '当前状态不需要确认。',
            ]);
        }

        $target->forceFill([
            'status' => PublishTargetStatus::Confirmed,
            'confirmed_at' => now(),
        ])->save();

        $target->publishJob->refreshStatus();
    }

    /**
     * @param  list<string>  $platformKeys
     * @return list<string>
     */
    private function normalizeKeys(array $platformKeys, ?PublishCombination $combination): array
    {
        $keys = [];

        foreach ($platformKeys as $key) {
            if (is_string($key) && trim($key) !== '') {
                $keys[] = trim($key);
            }
        }

        foreach ($combination?->platforms ?? [] as $key) {
            if (is_string($key) && $key !== '') {
                $keys[] = $key;
            }
        }

        $keys = array_values(array_unique($keys));

        foreach ($keys as $key) {
            if (! $this->adapters->has($key)) {
                throw ValidationException::withMessages([
                    'platforms' => "未知平台：{$key}",
                ]);
            }
        }

        return $keys;
    }
}
