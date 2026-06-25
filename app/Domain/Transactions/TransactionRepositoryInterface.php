<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

use App\Domain\Finance\Money;

interface TransactionRepositoryInterface
{
    /**
     * @return list<Transaction>
     */
    public function listLatest(int $limit = 50): array;

    public function create(
        int $bankAccountId,
        int $categoryId,
        string $description,
        Money $amount,
        TransactionDirection $direction,
        TransactionStatus $status,
        string $competenceMonth,
        ?string $paymentDate,
    ): Transaction;
}
