<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Data;

use App\Domain\BankAccounts\BankAccountStatus;
use App\Domain\BankAccounts\BankAccountType;

final readonly class UpdateBankAccountData
{
    public function __construct(
        public string $name,
        public BankAccountType $type,
        public BankAccountStatus $status,
        public ?string $bankCode,
        public ?string $agency,
        public ?string $accountNumber,
        public ?string $accountDigit,
        public ?string $description,
    ) {}
}