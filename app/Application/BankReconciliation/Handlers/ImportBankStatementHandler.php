<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\Handlers;

use App\Application\BankReconciliation\DTOs\ImportBankStatementData;
use App\Domain\BankReconciliation\ReconciliationItem;
use App\Domain\BankReconciliation\ReconciliationRepositoryInterface;
use App\Domain\BankReconciliation\ReconciliationStatus;
use App\Domain\Shared\Money;

final readonly class ImportBankStatementHandler
{
    public function __construct(
        private ReconciliationRepositoryInterface $reconciliationRepository,
    ) {}

    public function handle(ImportBankStatementData $data): void
    {
        foreach ($data->items as $itemData) {
            $reconciliationItem = new ReconciliationItem(
                id: $itemData->id,
                bankAccountId: $data->bankAccountId,
                date: $itemData->date,
                description: $itemData->description,
                amount: Money::of($itemData->amount),
                status: ReconciliationStatus::PENDING,
                bankStatementId: $itemData->bankReference,
                notes: $itemData->notes,
            );

            $this->reconciliationRepository->save($reconciliationItem);
        }
    }
}