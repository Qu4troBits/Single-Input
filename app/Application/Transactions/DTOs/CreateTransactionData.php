<?php

declare(strict_types=1);

namespace App\Application\Transactions\DTOs;

use App\Domain\BankAccounts\BankAccountId;
use App\Domain\Categories\CategoryId;
use App\Domain\Shared\Money;
use App\Domain\Transactions\TransactionDirection;
use App\Domain\Transactions\TransactionStatus;

final readonly class CreateTransactionData
{
    public function __construct(
        public BankAccountId $bankAccountId,
        public CategoryId $categoryId,
        public string $description,
        public Money $amount,
        public TransactionDirection $direction,
        public string $competenceMonth, // Formato: YYYY-MM
        public ?\DateTimeImmutable $paymentDate = null,
    ) {}
}