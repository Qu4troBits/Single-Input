<?php

declare(strict_types=1);

namespace App\Application\Transactions\CreateTransaction;

use App\Domain\Finance\Money;
use App\Domain\Transactions\TransactionDirection;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Domain\Transactions\TransactionStatus;
use RuntimeException;

final readonly class CreateTransactionHandler
{
    public function __construct(private TransactionRepositoryInterface $transactions)
    {
    }

    public function handle(CreateTransactionData $data): void
    {
        $direction = TransactionDirection::tryFrom($data->direction);
        $status = TransactionStatus::tryFrom($data->status);

        if ($direction === null || $status === null) {
            throw new RuntimeException('Invalid transaction data.');
        }

        $this->transactions->create(
            bankAccountId: $data->bankAccountId,
            categoryId: $data->categoryId,
            description: $data->description,
            amount: Money::of($data->amount),
            direction: $direction,
            status: $status,
            competenceMonth: $data->competenceMonth,
            paymentDate: $data->paymentDate,
        );
    }
}
