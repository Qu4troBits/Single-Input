<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Reports\Data\GenerateDreData;
use App\Application\Reports\Handlers\GenerateDreHandler;
use App\Domain\Reports\FinancialReportRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Inertia\Inertia;

final class ReportsController extends Controller
{
    public function index(FinancialReportRepositoryInterface $repository): Response
    {
        $availablePeriods = $repository->getAvailableReportPeriods();
        
        return Inertia::render('Reports/Index', [
            'availablePeriods' => $availablePeriods,
        ]);
    }

    public function showMonthlyDre(
        string $yearMonth,
        GenerateDreHandler $handler
    ): Response {
        $data = GenerateDreData::forMonthly($yearMonth);
        $report = $handler->handle($data);
        
        return Inertia::render('Reports/MonthlyDre', [
            'report' => $report->toArray(),
            'period' => $yearMonth,
        ]);
    }

    public function showQuarterlyDre(
        string $year,
        int $quarter,
        GenerateDreHandler $handler
    ): Response {
        $data = GenerateDreData::forQuarterly($year, $quarter);
        $report = $handler->handle($data);
        
        return Inertia::render('Reports/QuarterlyDre', [
            'report' => $report->toArray(),
            'period' => "{$year} Q{$quarter}",
        ]);
    }

    public function showYearlyDre(
        string $year,
        GenerateDreHandler $handler
    ): Response {
        $data = GenerateDreData::forYearly($year);
        $report = $handler->handle($data);
        
        return Inertia::render('Reports/YearlyDre', [
            'report' => $report->toArray(),
            'period' => $year,
        ]);
    }

    public function generateCustomDre(
        GenerateDreHandler $handler
    ): Response|RedirectResponse {
        $request = request();
        
        $data = GenerateDreData::forCustom(
            new \DateTimeImmutable($request->input('start_date')),
            new \DateTimeImmutable($request->input('end_date'))
        );
        
        $report = $handler->handle($data);
        
        return Inertia::render('Reports/CustomDre', [
            'report' => $report->toArray(),
            'period' => $report->getPeriod()->toString(),
        ]);
    }

    public function showProfitMarginTrend(
        FinancialReportRepositoryInterface $repository
    ): Response {
        $request = request();
        
        $startYearMonth = $request->input('start_year_month', date('Y-m', strtotime('-5 months')));
        $endYearMonth = $request->input('end_year_month', date('Y-m'));
        
        $trendData = $repository->getProfitMarginTrend($startYearMonth, $endYearMonth);
        
        return Inertia::render('Reports/ProfitMarginTrend', [
            'trendData' => $trendData,
            'startYearMonth' => $startYearMonth,
            'endYearMonth' => $endYearMonth,
        ]);
    }

    public function showRevenueByCategory(
        FinancialReportRepositoryInterface $repository
    ): Response {
        $request = request();
        
        $yearMonth = $request->input('period', date('Y-m'));
        $period = \App\Domain\Reports\ReportPeriod::createMonthly($yearMonth);
        
        $revenueByCategory = $repository->getRevenueByCategory($period);
        
        return Inertia::render('Reports/RevenueByCategory', [
            'revenueByCategory' => $revenueByCategory,
            'period' => $yearMonth,
        ]);
    }

    public function showExpensesByCategory(
        FinancialReportRepositoryInterface $repository
    ): Response {
        $request = request();
        
        $yearMonth = $request->input('period', date('Y-m'));
        $period = \App\Domain\Reports\ReportPeriod::createMonthly($yearMonth);
        
        $expensesByCategory = $repository->getExpensesByCategory($period);
        
        return Inertia::render('Reports/ExpensesByCategory', [
            'expensesByCategory' => $expensesByCategory,
            'period' => $yearMonth,
        ]);
    }
}