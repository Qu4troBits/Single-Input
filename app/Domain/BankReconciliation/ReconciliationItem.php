<?php

declare(strict_types=1);

namespace App\Domain\BankReconciliation;

use App\Domain\Shared\Money;

final readonly class ReconciliationItem
{
    public function __construct(
        private string $id,
        private string $bankAccountId,
        private \DateTimeImmutable $date,
        private string $description,
        private Money $amount,
        private ReconciliationStatus $status,
        private ?string $transactionId = null,
        private ?string $bankStatementId = null,
        private ?string $notes = null,
    ) {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Reconciliation item ID cannot be empty.');
        }

        if (empty($this->bankAccountId)) {
            throw new \InvalidArgumentException('Bank account ID cannot be empty.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }
    }

    public static function createFromTransaction(
        string $id,
        string $bankAccountId,
        \DateTimeImmutable $date,
        string $description,
        Money $amount,
        string $transactionId,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            bankAccountId: $bankAccountId,
            date: $date,
            description: $description,
            amount: $amount,
            status: ReconciliationStatus::PENDING,
            transactionId: $transactionId,
            bankStatementId: null,
            notes: $notes,
        );
    }

    public static function createFromBankStatement(
        string $id,
        string $bankAccountId,
        \DateTimeImmutable $date,
        string $description,
        Money $amount,
        string $bankStatementId,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            bankAccountId: $bankAccountId,
            date: $date,
            description: $description,
            amount: $amount,
            status: ReconciliationStatus::PENDING,
            transactionId: null,
            bankStatementId: $bankStatementId,
            notes: $notes,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBankAccountId(): string
    {
        return $this->bankAccountId;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getStatus(): ReconciliationStatus
    {
        return $this->status;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getBankStatementId(): ?string
    {
        return $this->bankStatementId;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function markAsReconciled(string $transactionId): self
    {
        return new self(
            id: $this->id,
            bankAccountId: $this->bankAccountId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            status: ReconciliationStatus::RECONCILED,
            transactionId: $transactionId,
            bankStatementId: $this->bankStatementId,
            notes: $this->notes,
        );
    }

    public function markAsUnreconciled(): self
    {
        return new self(
            id: $this->id,
            bankAccountId: $this->bankAccountId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            status: ReconciliationStatus::PENDING,
            transactionId: $this->transactionId,
            bankStatementId: $this->bankStatementId,
            notes: $this->notes,
        );
    }

    public function updateNotes(string $notes): self
    {
        return new self(
            id: $this->id,
            bankAccountId: $this->bankAccountId,
            date: $this->date,
            description: $this->description,
            amount: $this->amount,
            status: $this->status,
            transactionId: $this->transactionId,
            bankStatementId: $this->bankStatementId,
            notes: $notes,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bank_account_id' => $this->bankAccountId,
            'date' => $this->date->format('Y-m-d'),
            'description' => $this->description,
            'amount' => $this->amount->toNumeric(),
            'status' => $this->status->value,
            'transaction_id' => $this->transactionId,
            'bank_statement_id' => $this->bankStatementId,
            'notes' => $this->notes,
        ];
    }
}