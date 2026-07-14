<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections;

interface FinancialProjectionRepositoryInterface
{
    public function generateRevenueProjection(ProjectionPeriod $period, ?string $categoryId = null): FinancialProjection;
    
    public function generateExpenseProjection(ProjectionPeriod $period, ?string $categoryId = null): FinancialProjection;
    
    public function generateProfitProjection(ProjectionPeriod $period): FinancialProjection;
    
    public function generateCashFlowProjection(ProjectionPeriod $period): FinancialProjection;
    
    public function generateBalanceSheetProjection(ProjectionPeriod $period): FinancialProjection;
    
    /**
     * @return array<FinancialProjection>
     */
    public function findProjectionsByPeriod(ProjectionPeriod $period): array;
    
    public function save(FinancialProjection $projection): void;
    
    public function delete(string $projectionId): void;
    
    /**
     * @return array<string>
     */
    public function getAvailableScenarios(): array;
}