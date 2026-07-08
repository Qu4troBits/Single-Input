<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\Jobs;

use App\Domain\BankReconciliation\ReconciliationRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class GenerateReconciliationReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public array $backoff = [30, 60, 120];

    public function __construct(
        private readonly string $bankAccountId,
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly string $tenantId,
    ) {
        $this->onQueue('reports');
    }

    public function handle(
        ReconciliationRepositoryInterface $repository,
    ): void {
        try {
            Log::info('Starting reconciliation report generation job', [
                'tenant_id' => $this->tenantId,
                'bank_account_id' => $this->bankAccountId,
                'period' => "{$this->startDate} to {$this->endDate}",
            ]);

            $bankAccountId = BankAccountId::fromString($this->bankAccountId);
            $startDate = new \DateTimeImmutable($this->startDate);
            $endDate = new \DateTimeImmutable($this->endDate);

            $report = $repository->generateSummary(
                $bankAccountId->toString(),
                $startDate,
                $endDate
            );

            // Store report in storage
            $reportPath = "reports/reconciliation/{$this->tenantId}/{$this->bankAccountId}/";
            $reportFilename = "report-{$this->startDate}-{$this->endDate}.json";
            
            Storage::put(
                $reportPath . $reportFilename,
                json_encode($report, JSON_PRETTY_PRINT)
            );

            Log::info('Reconciliation report generated successfully', [
                'tenant_id' => $this->tenantId,
                'bank_account_id' => $this->bankAccountId,
                'report_path' => $reportPath . $reportFilename,
            ]);
        } catch (\Exception $e) {
            Log::error('Reconciliation report generation failed', [
                'tenant_id' => $this->tenantId,
                'bank_account_id' => $this->bankAccountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Reconciliation report generation job failed permanently', [
            'tenant_id' => $this->tenantId,
            'bank_account_id' => $this->bankAccountId,
            'error' => $exception->getMessage(),
        ]);
    }
}