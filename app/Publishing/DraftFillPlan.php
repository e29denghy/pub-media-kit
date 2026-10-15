<?php

namespace App\Publishing;

use App\Enums\PublishMode;

final class DraftFillPlan
{
    public const READY = 'ready';

    public const STUB = 'stub';

    /**
     * @param  list<FillStep>  $steps
     * @param  list<string>  $warnings
     * @param  list<string>  $warningsEn
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $platform,
        public readonly PublishMode $mode,
        public readonly string $outcome,
        public readonly string $summary,
        public readonly string $summaryEn,
        public readonly array $steps,
        public readonly bool $requiresHumanConfirm,
        public readonly array $warnings = [],
        public readonly array $warningsEn = [],
        public readonly array $meta = [],
        public readonly bool $networkCalled = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'mode' => $this->mode->value,
            'outcome' => $this->outcome,
            'summary' => $this->summary,
            'summary_en' => $this->summaryEn,
            'steps' => array_map(fn (FillStep $step) => $step->toArray(), $this->steps),
            'requires_human_confirm' => $this->requiresHumanConfirm,
            'warnings' => $this->warnings,
            'warnings_en' => $this->warningsEn,
            'meta' => $this->meta,
            'network_called' => $this->networkCalled,
        ];
    }
}
