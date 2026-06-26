<?php

declare(strict_types=1);

namespace App\Application\Transactions\Handlers;

use App\Application\Transactions\Data\CreateTransactionData;
use App\Domain\Transactions\Transaction;
use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Domain\Transactions\TransactionStatus;

final readonly class CreateTransactionHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function handle(CreateTransactionData $data): TransactionId
    {
        $transaction = new Transaction(
            id: TransactionId::generate(),
            bankAccountId: $data->bankAccountId,
            categoryId: $data->categoryId,
            description: $data->description,
            amount: $data->amount,
            direction: $data->direction,
            status: TransactionStatus::PENDING,
            competenceMonth: $data->competenceMonth,
            paymentDate: $data->paymentDate,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->transactionRepository->save($transaction);
        return $transaction->getId();
    }
}