<?php

namespace App\Policies;

use App\Models\PublishCombination;
use App\Models\User;

class PublishCombinationPolicy
{
    public function delete(User $user, PublishCombination $combination): bool
    {
        return $combination->user_id === $user->id;
    }
}
