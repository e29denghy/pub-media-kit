<?php

namespace App\Policies;

use App\Models\PublishJob;
use App\Models\User;

class PublishJobPolicy
{
    public function view(User $user, PublishJob $job): bool
    {
        return $job->user_id === $user->id;
    }
}
