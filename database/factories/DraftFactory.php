<?php

namespace Database\Factories;

use App\Enums\DraftStatus;
use App\Models\Draft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Draft>
 */
class DraftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(2, true),
            'body_format' => 'markdown',
            'tags' => ['示例'],
            'status' => DraftStatus::Draft,
        ];
    }
}
