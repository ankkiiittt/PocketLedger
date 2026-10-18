<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUser
{
    public function __construct(
        public SeedDefaultCategories $seedDefaultCategories
    ) {}

    /**
     * Create user and seed default categories in a single transaction.
     *
     * @param  array{name: string, email: string, password: string, currency_code?: string}  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'currency_code' => strtoupper($data['currency_code'] ?? 'INR'),
            ]);

            $this->seedDefaultCategories->execute($user);

            return $user;
        });
    }
}
