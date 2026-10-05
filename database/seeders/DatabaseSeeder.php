<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * The password shared by the demo accounts.
     */
    public const DEMO_PASSWORD = 'Demo-Passw0rd!';

    /**
     * Seed the application's database with demo accounts.
     */
    public function run(): void
    {
        // The demo password is public, so these accounts must never exist in production.
        if (app()->isProduction()) {
            $this->command?->warn('Demo accounts are not seeded in production.');

            return;
        }

        $password = Hash::make(self::DEMO_PASSWORD);

        User::factory()->admin()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@example.com',
            'password' => $password,
        ]);

        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'user@example.com',
            'password' => $password,
        ]);

        User::factory(18)->create(['password' => $password]);
    }
}
