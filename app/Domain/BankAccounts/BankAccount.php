<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts;

use App\Domain\Shared\Money;

final class BankAccount
{
    private Money $balance;
    private Money $initialBalance;

    public function __construct(
        private readonly BankAccountId $id,
        private string $name,
        private BankAccountType $type,
        private BankAccountStatus $status,
        private ?string $bankCode,
        private ?string $agency,
        private ?string $accountNumber,
        private ?string $accountDigit,
        private ?string $description,
        Money $initialBalance,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
        $this->initialBalance = $initialBalance;
        $this->balance = $initialBalance;
    }

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

    public function getStatus(): BankAccountStatus
    {
        return $this->status;
    }

    public function getBankCode(): ?string
    {
        return $this->bankCode;
    }

    public function getAgency(): ?string
    {
        return $this->agency;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function getAccountDigit(): ?string
    {
        return $this->accountDigit;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function getInitialBalance(): Money
    {
        return $this->initialBalance;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(
        string $name,
        BankAccountType $type,
        BankAccountStatus $status,
        ?string $bankCode,
        ?string $agency,
        ?string $accountNumber,
        ?string $accountDigit,
        ?string $description,
    ): void {
        $this->name = $name;
        $this->type = $type;
        $this->status = $status;
        $this->bankCode = $bankCode;
        $this->agency = $agency;
        $this->accountNumber = $accountNumber;
        $this->accountDigit = $accountDigit;
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateBalance(Money $newBalance): void
    {
        $this->balance = $newBalance;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->status === BankAccountStatus::ACTIVE;
    }

    public function isChecking(): bool
    {
        return $this->type === BankAccountType::CHECKING;
    }

    public function isCreditCard(): bool
    {
        return $this->type === BankAccountType::CREDIT_CARD;
    }
}