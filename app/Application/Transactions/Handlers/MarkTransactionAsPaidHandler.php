<?php

declare(strict_types=1);

namespace App\Application\Transactions\Handlers;

use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionRepositoryInterface;
use RuntimeException;

final readonly class MarkTransactionAsPaidHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function handle(TransactionId $id, \DateTimeImmutable $paymentDate): void
    {
        $transaction = $this->transactionRepository->findById($id);

        if ($transaction === null) {
            throw new RuntimeException('Transaction not found.');
        }

        $transaction->markAsPaid($paymentDate);
        $this->transactionRepository->save($transaction);
    }
}