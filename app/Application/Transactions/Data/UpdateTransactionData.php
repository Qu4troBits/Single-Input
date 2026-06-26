<?php

declare(strict_types=1);

namespace App\Application\Transactions\Data;

use App\Domain\BankAccounts\BankAccountId;
use App\Domain\Categories\CategoryId;
use App\Domain\Shared\Money;
use App\Domain\Transactions\TransactionDirection;

final readonly class UpdateTransactionData
{
    public function __construct(
        public BankAccountId $bankAccountId,
        public CategoryId $categoryId,
        public string $description,
        public Money $amount,
        public TransactionDirection $direction,
        public string $competenceMonth, // Formato: YYYY-MM
    ) {}
}