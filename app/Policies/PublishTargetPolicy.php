<?php

namespace App\Policies;

use App\Models\PublishTarget;
use App\Models\User;

class PublishTargetPolicy
{
    public function confirm(User $user, PublishTarget $target): bool
    {
        return $target->publishJob?->user_id === $user->id;
    }
}
