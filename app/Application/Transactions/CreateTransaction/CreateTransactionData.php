<?php

declare(strict_types=1);

namespace App\Application\Transactions\CreateTransaction;

final readonly class CreateTransactionData
{
    public function __construct(
        public int $bankAccountId,
        public int $categoryId,
        public string $description,
        public string $amount,
        public string $direction,
        public string $status,
        public string $competenceMonth,
        public ?string $paymentDate,
    ) {
    }
}
