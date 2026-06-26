<?php

declare(strict_types=1);

namespace App\Application\Transactions\Handlers;

use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionRepositoryInterface;
use RuntimeException;

final readonly class MarkTransactionAsCancelledHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function handle(TransactionId $id): void
    {
        $transaction = $this->transactionRepository->findById($id);

        if ($transaction === null) {
            throw new RuntimeException('Transaction not found.');
        }

        $transaction->markAsCancelled();
        $this->transactionRepository->save($transaction);
    }
}