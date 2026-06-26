<?php

declare(strict_types=1);

namespace App\Domain\Reports;

interface FinancialReportRepositoryInterface
{
    public function generateMonthlyDre(string $yearMonth): FinancialReport;
    
    public function generateQuarterlyDre(string $year, int $quarter): FinancialReport;
    
    public function generateYearlyDre(string $year): FinancialReport;
    
    public function generateCustomPeriodDre(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): FinancialReport;
    
    /** @return array<string> */
    public function getAvailableReportPeriods(): array;
    
    public function getRevenueByCategory(ReportPeriod $period): array;
    
    public function getExpensesByCategory(ReportPeriod $period): array;
    
    public function getProfitMarginTrend(string $startYearMonth, string $endYearMonth): array;
}