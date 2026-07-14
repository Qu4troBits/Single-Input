<?php

declare(strict_types=1);

namespace App\Application\Transactions\Handlers;

use App\Application\Transactions\DTOs\UpdateTransactionData;
use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionRepositoryInterface;
use RuntimeException;

final readonly class UpdateTransactionHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function handle(TransactionId $id, UpdateTransactionData $data): void
    {
        $transaction = $this->transactionRepository->findById($id);

        if ($transaction === null) {
            throw new RuntimeException('Transaction not found.');
        }

        $transaction->update(
            bankAccountId: $data->bankAccountId,
            categoryId: $data->categoryId,
            description: $data->description,
            amount: $data->amount,
            direction: $data->direction,
            competenceMonth: $data->competenceMonth,
        );

        $this->transactionRepository->save($transaction);
    }
}