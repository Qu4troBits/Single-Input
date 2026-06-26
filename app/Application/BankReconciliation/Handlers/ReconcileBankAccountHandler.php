<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\Handlers;

use App\Application\BankReconciliation\Data\ReconcileBankAccountData;
use App\Domain\BankReconciliation\ReconciliationItem;
use App\Domain\BankReconciliation\ReconciliationRepositoryInterface;
use App\Domain\Shared\Money;

final readonly class ReconcileBankAccountHandler
{
    public function __construct(
        private ReconciliationRepositoryInterface $reconciliationRepository,
    ) {}

    public function handle(ReconcileBankAccountData $data): void
    {
        foreach ($data->items as $itemData) {
            $reconciliationItem = new ReconciliationItem(
                id: $itemData->id,
                bankAccountId: $data->bankAccountId,
                date: $itemData->date,
                description: $itemData->description,
                amount: Money::of($itemData->amount),
                status: $itemData->status,
                transactionId: $itemData->transactionId,
                bankStatementId: $itemData->bankStatementId,
                notes: $itemData->notes,
            );

            $this->reconciliationRepository->save($reconciliationItem);
        }
    }
}