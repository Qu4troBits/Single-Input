<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Data;

use App\Domain\BankAccounts\BankAccountType;
use App\Domain\Shared\Money;

final readonly class CreateBankAccountData
{
    public function __construct(
        public string $name,
        public BankAccountType $type,
        public ?string $bankCode,
        public ?string $agency,
        public ?string $accountNumber,
        public ?string $accountDigit,
        public ?string $description,
        public Money $initialBalance,
    ) {}
}