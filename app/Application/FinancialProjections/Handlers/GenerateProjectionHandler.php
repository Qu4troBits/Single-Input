<?php

declare(strict_types=1);

namespace App\Application\FinancialProjections\Handlers;

use App\Application\FinancialProjections\Data\GenerateProjectionData;
use App\Domain\FinancialProjections\FinancialProjection;
use App\Domain\FinancialProjections\PeriodType;
use App\Domain\FinancialProjections\ProjectionPeriod;
use App\Domain\FinancialProjections\Services\FinancialProjectionGenerator;

final readonly class GenerateProjectionHandler
{
    public function __construct(
        private FinancialProjectionGenerator $projectionGenerator,
    ) {}

    public function handle(GenerateProjectionData $data): FinancialProjection
    {
        $period = $this->createProjectionPeriod($data);

        return match ($data->type) {
            \App\Domain\FinancialProjections\ProjectionType::REVENUE => 
                $this->projectionGenerator->generateRevenueProjection($period, $data->categoryId),
            \App\Domain\FinancialProjections\ProjectionType::EXPENSE => 
                $this->projectionGenerator->generateExpenseProjection($period, $data->categoryId),
            \App\Domain\FinancialProjections\ProjectionType::PROFIT => 
                $this->projectionGenerator->generateProfitProjection($period),
            \App\Domain\FinancialProjections\ProjectionType::CASH_FLOW => 
                $this->projectionGenerator->generateCashFlowProjection($period),
            \App\Domain\FinancialProjections\ProjectionType::BALANCE_SHEET => 
                $this->projectionGenerator->generateBalanceSheetProjection($period),
        };
    }

    private function createProjectionPeriod(GenerateProjectionData $data): ProjectionPeriod
    {
        return match ($data->periodType) {
            'monthly' => ProjectionPeriod::createMonthly($data->yearMonth),
            'quarterly' => ProjectionPeriod::createQuarterly($data->year, $data->quarter),
            'yearly' => ProjectionPeriod::createYearly($data->year),
            default => throw new \InvalidArgumentException('Invalid period type.'),
        };
    }
}