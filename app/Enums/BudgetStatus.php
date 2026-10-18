<?php

declare(strict_types=1);

namespace App\Enums;

enum BudgetStatus: string
{
    case OnTrack = 'on_track';
    case NearingLimit = 'nearing_limit';
    case OverBudget = 'over_budget';

    public function label(): string
    {
        return match ($this) {
            self::OnTrack => 'On track',
            self::NearingLimit => 'Nearing limit',
            self::OverBudget => 'Over budget',
        };
    }
}
