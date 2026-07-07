<?php

declare(strict_types=1);

namespace App\Application\Reports\Jobs;

use App\Application\Reports\DTOs\GenerateDreData;
use App\Application\Reports\Handlers\GenerateDreHandler;
use App\Domain\Reports\DreRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class CalculateDreJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;
    public int $backoff = [30, 60, 120];

    public function __construct(
        private readonly GenerateDreData $data,
        private readonly string $tenantId,
    ) {
        $this->onQueue('reports');
    }

    public function handle(
        GenerateDreHandler $handler,
        DreRepositoryInterface $repository,
    ): void {
        try {
            Log::info('Starting DRE calculation job', [
                'tenant_id' => $this->tenantId,
                'period_type' => $this->data->periodType,
            ]);

            $dre = $handler->handle($this->data);

            $repository->save($dre);

            Log::info('DRE calculation completed successfully', [
                'tenant_id' => $this->tenantId,
                'dre_id' => $dre->id->toString(),
            ]);
        } catch (\Exception $e) {
            Log::error('DRE calculation failed', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('DRE calculation job failed permanently', [
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
        ]);
    }
}