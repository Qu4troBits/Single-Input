<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\DTOs;

use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;

final class UpdateBankAccountData
{
    public function __construct(
        public readonly BankAccountId $id,
        public readonly string $name,
        public readonly BankAccountType $type,
        public readonly string $bankCode,
        public readonly string $bankName,
        public readonly string $agencyNumber,
        public readonly string $accountNumber,
        public readonly ?string $accountDigit,
        public readonly Money $initialBalance,
        public readonly ?string $description = null,
        public readonly ?string $color = null,
        public readonly ?string $icon = null,
        public readonly bool $includeInDashboard = true,
        public readonly bool $includeInReports = true,
        public readonly bool $isDefault = false,
    ) {}
}
