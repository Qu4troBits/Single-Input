<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

use App\Domain\Finance\Money;

final readonly class Transaction
{
    public function __construct(
        public int $id,
        public int $bankAccountId,
        public int $categoryId,
        public string $description,
        public Money $amount,
        public TransactionDirection $direction,
        public TransactionStatus $status,
        public string $competenceMonth,
        public ?string $paymentDate,
    ) {
    }
}
