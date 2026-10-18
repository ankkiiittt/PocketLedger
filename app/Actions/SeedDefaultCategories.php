<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CategoryType;
use App\Models\User;

class SeedDefaultCategories
{
    /**
     * The 8 preset palette colours in cycle order.
     *
     * @var list<string>
     */
    public const PALETTE = [
        '#1F5F4D', // pine
        '#B4432F', // brick
        '#2F5F8A', // slate blue
        '#B7791F', // ochre
        '#6B4E8F', // plum
        '#3D8B8B', // teal
        '#8A5A44', // umber
        '#6F7D3A', // olive
    ];

    /**
     * Default categories in creation order.
     *
     * @var list<array{name: string, type: CategoryType}>
     */
    public const DEFAULT_CATEGORIES = [
        // Expenses (10)
        ['name' => 'Groceries', 'type' => CategoryType::Expense],
        ['name' => 'Rent', 'type' => CategoryType::Expense],
        ['name' => 'Utilities', 'type' => CategoryType::Expense],
        ['name' => 'Transport', 'type' => CategoryType::Expense],
        ['name' => 'Dining Out', 'type' => CategoryType::Expense],
        ['name' => 'Health', 'type' => CategoryType::Expense],
        ['name' => 'Entertainment', 'type' => CategoryType::Expense],
        ['name' => 'Shopping', 'type' => CategoryType::Expense],
        ['name' => 'Education', 'type' => CategoryType::Expense],
        ['name' => 'Other', 'type' => CategoryType::Expense],
        // Income (4)
        ['name' => 'Salary', 'type' => CategoryType::Income],
        ['name' => 'Freelance', 'type' => CategoryType::Income],
        ['name' => 'Interest', 'type' => CategoryType::Income],
        ['name' => 'Other Income', 'type' => CategoryType::Income],
    ];

    /**
     * Seed default categories for a user.
     */
    public function execute(User $user): void
    {
        $paletteCount = count(self::PALETTE);

        foreach (self::DEFAULT_CATEGORIES as $index => $item) {
            $color = self::PALETTE[$index % $paletteCount];

            $user->categories()->create([
                'name' => $item['name'],
                'type' => $item['type'],
                'color' => $color,
                'is_archived' => false,
            ]);
        }
    }
}
