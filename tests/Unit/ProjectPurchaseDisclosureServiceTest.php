<?php

use App\Models\Project;
use App\Services\ProjectPurchaseDisclosureService;

uses(Tests\TestCase::class);

test('it mirrors the checkout subtotal tax and total calculation', function () {
    $project = new Project([
        'price_per_ton' => 255000,
        'stock_available' => 69,
    ]);

    $disclosure = app(ProjectPurchaseDisclosureService::class)
        ->forProject($project, 2);

    expect($disclosure['quantity_ton'])->toBe(2)
        ->and($disclosure['subtotal'])->toBe(510000.0)
        ->and($disclosure['tax_rate_percentage'])->toBe(11.0)
        ->and($disclosure['tax'])->toBe(56100.0)
        ->and($disclosure['other_fees'])->toBe(0)
        ->and($disclosure['estimated_total'])->toBe(566100.0)
        ->and($disclosure['risks'])->toHaveCount(3)
        ->and($disclosure['terms'])->toHaveCount(4);
});
