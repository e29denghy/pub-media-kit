<?php

namespace Database\Seeders;

use App\Enums\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local demo account. Password is the factory default "password".
     * Do not use this seeder on a public host.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Local Admin',
            'email' => 'admin@example.com',
        ]);

        $admin->publishCombinations()->create([
            'name' => '图文双发',
            'platforms' => [
                Platform::WechatOfficialAccount->value,
                Platform::Zhihu->value,
            ],
            'notes' => '阶段一默认组合：微信公众号 + 知乎。两边都是网页填草稿，人确认后才发表。',
        ]);
    }
}
