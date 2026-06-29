<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts\Repositories;

use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;

interface BankAccountRepositoryInterface
{
    public function findById(BankAccountId $id): ?BankAccount;

    public function findByAccountNumber(string $bankCode, string $agencyNumber, string $accountNumber): ?BankAccount;

    public function findAll(
        ?BankAccountType $type = null,
        ?BankAccountStatus $status = null,
        bool $includeInDashboard = false,
        bool $includeInReports = false,
        bool $isDefault = false,
        int $page = 1,
        int $perPage = 20
    ): array;

    public function findAllActive(): array;

    public function findAllForDashboard(): array;

    public function findAllForReports(): array;

    public function getDefaultAccount(): ?BankAccount;

    public function save(BankAccount $bankAccount): void;

    public function delete(BankAccount $bankAccount): void;

    public function updateBalance(BankAccountId $id, Money $newBalance): void;

    public function existsWithAccountNumber(
        string $bankCode,
        string $agencyNumber,
        string $accountNumber,
        ?BankAccountId $excludeId = null
    ): bool;

    public function countByType(BankAccountType $type): int;

    public function countByStatus(BankAccountStatus $status): int;

    public function getTotalBalance(): Money;

    public function getTotalBalanceByType(BankAccountType $type): Money;
}
