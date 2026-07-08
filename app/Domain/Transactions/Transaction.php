<?php

declare(strict_types=1);

namespace App\Domain\Transactions;

use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Shared\Money;

final class Transaction
{
    public function __construct(
        private readonly TransactionId $id,
        private BankAccountId $bankAccountId,
        private CategoryId $categoryId,
        private string $description,
        private Money $amount,
        private TransactionDirection $direction,
        private TransactionStatus $status,
        private string $competenceMonth, // Formato: YYYY-MM
        private ?\DateTimeImmutable $paymentDate,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): TransactionId
    {
        return $this->id;
    }

    public function getBankAccountId(): BankAccountId
    {
        return $this->bankAccountId;
    }

    public function getCategoryId(): CategoryId
    {
        return $this->categoryId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getDirection(): TransactionDirection
    {
        return $this->direction;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function getCompetenceMonth(): string
    {
        return $this->competenceMonth;
    }

    public function getPaymentDate(): ?\DateTimeImmutable
    {
        return $this->paymentDate;
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
        BankAccountId $bankAccountId,
        CategoryId $categoryId,
        string $description,
        Money $amount,
        TransactionDirection $direction,
        string $competenceMonth,
    ): void {
        $this->bankAccountId = $bankAccountId;
        $this->categoryId = $categoryId;
        $this->description = $description;
        $this->amount = $amount;
        $this->direction = $direction;
        $this->competenceMonth = $competenceMonth;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsPaid(\DateTimeImmutable $paymentDate): void
    {
        if (!$this->status->canBePaid()) {
            throw new \RuntimeException('Transaction cannot be marked as paid.');
        }

        $this->status = TransactionStatus::PAID;
        $this->paymentDate = $paymentDate;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsCancelled(): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new \RuntimeException('Transaction cannot be cancelled.');
        }

        $this->status = TransactionStatus::CANCELLED;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isIncome(): bool
    {
        return $this->direction->isIncome();
    }

    public function isExpense(): bool
    {
        return $this->direction->isExpense();
    }

    public function getSignedAmount(): Money
    {
        if ($this->direction->isIncome()) {
            return $this->amount;
        }
        
        return $this->amount->multiply('-1');
    }
}