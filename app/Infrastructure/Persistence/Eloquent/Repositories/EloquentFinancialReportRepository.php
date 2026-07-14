<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Reports\FinancialReport;
use App\Domain\Reports\FinancialReportRepositoryInterface;
use App\Domain\Reports\ReportPeriod;
use App\Domain\Reports\Services\FinancialReportGenerator;
use App\Infrastructure\Persistence\Eloquent\Models\TransactionModel;
use Illuminate\Support\Facades\DB;

final class EloquentFinancialReportRepository implements FinancialReportRepositoryInterface
{
    public function __construct(
        private FinancialReportGenerator $reportGenerator,
    ) {}

    public function generateMonthlyDre(string $yearMonth): FinancialReport
    {
        $period = ReportPeriod::createMonthly($yearMonth);
        return $this->reportGenerator->generateDre($period);
    }

    public function generateQuarterlyDre(string $year, int $quarter): FinancialReport
    {
        $period = ReportPeriod::createQuarterly($year, $quarter);
        return $this->reportGenerator->generateDre($period);
    }

    public function generateYearlyDre(string $year): FinancialReport
    {
        $period = ReportPeriod::createYearly($year);
        return $this->reportGenerator->generateDre($period);
    }

    public function generateCustomPeriodDre(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): FinancialReport
    {
        $period = new ReportPeriod($startDate, $endDate);
        return $this->reportGenerator->generateDre($period);
    }

    public function getAvailableReportPeriods(): array
    {
        $periods = TransactionModel::selectRaw("DISTINCT TO_CHAR(created_at, 'YYYY-MM') as month")
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->toArray();

        return $periods;
    }

    public function getRevenueByCategory(ReportPeriod $period): array
    {
        $revenueByCategory = TransactionModel::select([
                'category_id',
                DB::raw("SUM(amount) as total_amount"),
            ])
            ->whereBetween('created_at', [
                $period->getStartDate()->format('Y-m-d H:i:s'),
                $period->getEndDate()->format('Y-m-d H:i:s'),
            ])
            ->where('direction', 'in')
            ->where('status', 'paid')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_id,
                    'total_amount' => $item->total_amount,
                ];
            })
            ->toArray();

        return $revenueByCategory;
    }

    public function getExpensesByCategory(ReportPeriod $period): array
    {
        $expensesByCategory = TransactionModel::select([
                'category_id',
                DB::raw("SUM(amount) as total_amount"),
            ])
            ->whereBetween('created_at', [
                $period->getStartDate()->format('Y-m-d H:i:s'),
                $period->getEndDate()->format('Y-m-d H:i:s'),
            ])
            ->where('direction', 'out')
            ->where('status', 'paid')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_id,
                    'total_amount' => $item->total_amount,
                ];
            })
            ->toArray();

        return $expensesByCategory;
    }

    public function getProfitMarginTrend(string $startYearMonth, string $endYearMonth): array
    {
        $startDate = \DateTimeImmutable::createFromFormat('Y-m', $startYearMonth)
            ->modify('first day of this month')
            ->setTime(0, 0, 0);
        
        $endDate = \DateTimeImmutable::createFromFormat('Y-m', $endYearMonth)
            ->modify('last day of this month')
            ->setTime(23, 59, 59);

        $trendData = [];

        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $period = ReportPeriod::createMonthly($currentDate->format('Y-m'));
            $report = $this->reportGenerator->generateDre($period);
            
            $totalRevenue = $report->getTotalRevenue();
            $netProfit = $report->getNetProfit();
            
            $profitMargin = $totalRevenue->isZero() 
                ? '0.00' 
                : $netProfit->divide($totalRevenue->toNumeric())->multiply('100')->toNumeric();
            
            $trendData[] = [
                'period' => $currentDate->format('Y-m'),
                'revenue' => $totalRevenue->toNumeric(),
                'net_profit' => $netProfit->toNumeric(),
                'profit_margin' => $profitMargin,
            ];
            
            $currentDate = $currentDate->modify('+1 month');
        }

        return $trendData;
    }
}