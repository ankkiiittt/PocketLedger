<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@pocketledger.test'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'currency_code' => 'INR',
            ]
        );

        if ($user->categories()->count() === 0) {
            app(\App\Actions\SeedDefaultCategories::class)->execute($user);
        }
    }
}
