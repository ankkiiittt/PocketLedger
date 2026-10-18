<?php

declare(strict_types=1);

use App\Enums\BudgetStatus;
use App\Services\BudgetStatusService;

test('BudgetStatusService returns OnTrack for percent under 80%', function () {
    $service = new BudgetStatusService;

    // 79.99% -> 799.90 of 1000.00
    expect($service->getStatus('799.90', '1000.00'))->toBe(BudgetStatus::OnTrack);
    // 50%
    expect($service->getStatus('500.00', '1000.00'))->toBe(BudgetStatus::OnTrack);
    // 0%
    expect($service->getStatus('0.00', '1000.00'))->toBe(BudgetStatus::OnTrack);
});

test('BudgetStatusService returns NearingLimit for 80% to 100%', function () {
    $service = new BudgetStatusService;

    // Exactly 80%
    expect($service->getStatus('800.00', '1000.00'))->toBe(BudgetStatus::NearingLimit);
    // 95%
    expect($service->getStatus('950.00', '1000.00'))->toBe(BudgetStatus::NearingLimit);
    // Exactly 100%
    expect($service->getStatus('1000.00', '1000.00'))->toBe(BudgetStatus::NearingLimit);
});

test('BudgetStatusService returns OverBudget for percent over 100%', function () {
    $service = new BudgetStatusService;

    // 100.01% -> 1000.10 of 1000.00
    expect($service->getStatus('1000.10', '1000.00'))->toBe(BudgetStatus::OverBudget);
    // 150%
    expect($service->getStatus('1500.00', '1000.00'))->toBe(BudgetStatus::OverBudget);
});

test('BudgetStatusService computes remaining and percent', function () {
    $service = new BudgetStatusService;

    expect($service->getRemaining('350.25', '1000.00'))->toBe('649.75');
    expect($service->getRemaining('1200.00', '1000.00'))->toBe('-200.00');
    expect($service->getPercent('250.00', '1000.00'))->toBe(25.0);
});
