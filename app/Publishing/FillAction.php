<?php

namespace App\Publishing;

/**
 * Stable action names for a future Playwright runner or browser extension.
 *
 * A runner should switch on these constants. This skeleton records the plan
 * and does not execute it.
 */
final class FillAction
{
    public const ENSURE_SESSION = 'ensure_session';

    public const OPEN_EDITOR = 'open_editor';

    public const FILL_FIELD = 'fill_field';

    public const ATTACH_MEDIA = 'attach_media';

    public const AWAIT_HUMAN_PUBLISH = 'await_human_publish';

    public const BUILD_PAYLOAD = 'build_payload';

    public const SKIP_NETWORK = 'skip_network';
}
