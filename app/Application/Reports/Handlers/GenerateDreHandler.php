<?php

declare(strict_types=1);

namespace App\Application\Reports\Handlers;

use App\Application\Reports\Data\GenerateDreData;
use App\Domain\Reports\FinancialReport;
use App\Domain\Reports\ReportPeriod;
use App\Domain\Reports\Services\FinancialReportGenerator;

final readonly class GenerateDreHandler
{
    public function __construct(
        private FinancialReportGenerator $reportGenerator,
    ) {}

    public function handle(GenerateDreData $data): FinancialReport
    {
        $period = $this->createReportPeriod($data);
        
        return $this->reportGenerator->generateDre($period);
    }
    
    private function createReportPeriod(GenerateDreData $data): ReportPeriod
    {
        return match ($data->periodType) {
            'monthly' => ReportPeriod::createMonthly($data->yearMonth),
            'quarterly' => ReportPeriod::createQuarterly($data->year, $data->quarter),
            'yearly' => ReportPeriod::createYearly($data->year),
            'custom' => new ReportPeriod($data->startDate, $data->endDate),
        };
    }
}