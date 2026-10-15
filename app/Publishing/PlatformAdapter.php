<?php

namespace App\Publishing;

use App\Enums\PublishMode;
use App\Models\Draft;

/**
 * One publishing line.
 *
 * prepareDraftFill() may only describe work. It must not publish and must
 * not perform network I/O. Browser lines stop before the platform's publish
 * button; a person confirms afterwards. Official API lines stay stubs until
 * a later phase adds an explicit publisher.
 */
interface PlatformAdapter
{
    public function key(): string;

    public function label(): string;

    public function labelZh(): string;

    public function description(): string;

    public function mode(): PublishMode;

    /**
     * 1 = phase-1 operator line (WeChat, Zhihu). 0 = registered stub.
     */
    public function phase(): int;

    public function publishesAutomatically(): bool;

    /**
     * @param  array<string, mixed>  $options
     */
    public function prepareDraftFill(Draft $draft, array $options = []): DraftFillPlan;
}
