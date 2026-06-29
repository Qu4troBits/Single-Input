<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts\Entities;

use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use DateTimeImmutable;

final class BankAccount
{
    private ?DateTimeImmutable $deletedAt = null;

    public function __construct(
        private BankAccountId $id,
        private string $name,
        private BankAccountType $type,
        private string $bankCode,
        private string $bankName,
        private string $agencyNumber,
        private string $accountNumber,
        private ?string $accountDigit,
        private Money $initialBalance,
        private Money $currentBalance,
        private BankAccountStatus $status,
        private ?string $description = null,
        private ?string $color = null,
        private ?string $icon = null,
        private bool $includeInDashboard = true,
        private bool $includeInReports = true,
        private bool $isDefault = false,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): BankAccountId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): BankAccountType
    {
        return $this->type;
    }

    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function getAgencyNumber(): string
    {
        return $this->agencyNumber;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getAccountDigit(): ?string
    {
        return $this->accountDigit;
    }

    public function getInitialBalance(): Money
    {
        return $this->initialBalance;
    }

    public function getCurrentBalance(): Money
    {
        return $this->currentBalance;
    }

    public function getStatus(): BankAccountStatus
    {
        return $this->status;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function isIncludeInDashboard(): bool
    {
        return $this->includeInDashboard;
    }

    public function isIncludeInReports(): bool
    {
        return $this->includeInReports;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function update(
        string $name,
        BankAccountType $type,
        string $bankCode,
        string $bankName,
        string $agencyNumber,
        string $accountNumber,
        ?string $accountDigit,
        Money $initialBalance,
        ?string $description = null,
        ?string $color = null,
        ?string $icon = null,
        bool $includeInDashboard = true,
        bool $includeInReports = true,
        bool $isDefault = false,
        DateTimeImmutable $updatedAt,
    ): void {
        $this->name = $name;
        $this->type = $type;
        $this->bankCode = $bankCode;
        $this->bankName = $bankName;
        $this->agencyNumber = $agencyNumber;
        $this->accountNumber = $accountNumber;
        $this->accountDigit = $accountDigit;
        $this->initialBalance = $initialBalance;
        $this->description = $description;
        $this->color = $color;
        $this->icon = $icon;
        $this->includeInDashboard = $includeInDashboard;
        $this->includeInReports = $includeInReports;
        $this->isDefault = $isDefault;
        $this->updatedAt = $updatedAt;
    }

    public function updateBalance(Money $newBalance, DateTimeImmutable $updatedAt): void
    {
        $this->currentBalance = $newBalance;
        $this->updatedAt = $updatedAt;
    }

    public function changeStatus(BankAccountStatus $status, DateTimeImmutable $updatedAt): void
    {
        $this->status = $status;
        $this->updatedAt = $updatedAt;
    }

    public function markAsDeleted(DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
        $this->status = BankAccountStatus::CLOSED;
        $this->updatedAt = $deletedAt;
    }

    public function getFullAccountNumber(): string
    {
        return $this->accountNumber . ($this->accountDigit ? '-' . $this->accountDigit : '');
    }

    public function getFullAgencyNumber(): string
    {
        return $this->agencyNumber;
    }

    public function getBalanceDifference(): Money
    {
        return $this->currentBalance->subtract($this->initialBalance);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function canReceiveTransactions(): bool
    {
        return $this->status->canReceiveTransactions();
    }
}
