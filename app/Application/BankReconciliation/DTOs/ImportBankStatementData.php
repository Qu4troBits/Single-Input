<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\DTOs;

final readonly class ImportBankStatementData
{
    /**
     * @param array<BankStatementItemData> $items
     */
    public function __construct(
        public string $bankAccountId,
        public \DateTimeImmutable $statementDate,
        public string $statementType, // 'csv', 'ofx', 'pdf', 'manual'
        public array $items,
        public ?string $originalFilename = null,
        public ?string $notes = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->bankAccountId)) {
            throw new \InvalidArgumentException('Bank account ID cannot be empty.');
        }

        if (empty($this->items)) {
            throw new \InvalidArgumentException('At least one bank statement item is required.');
        }

        $validStatementTypes = ['csv', 'ofx', 'pdf', 'manual'];
        if (!in_array($this->statementType, $validStatementTypes, true)) {
            throw new \InvalidArgumentException('Invalid statement type.');
        }

        foreach ($this->items as $item) {
            if (!$item instanceof BankStatementItemData) {
                throw new \InvalidArgumentException('All items must be instances of BankStatementItemData.');
            }
        }
    }
}

final readonly class BankStatementItemData
{
    public function __construct(
        public string $id,
        public \DateTimeImmutable $date,
        public string $description,
        public string $amount,
        public string $type, // 'credit', 'debit'
        public ?string $bankReference = null,
        public ?string $notes = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Item ID cannot be empty.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Item description cannot be empty.');
        }

        if (!is_numeric($this->amount)) {
            throw new \InvalidArgumentException('Amount must be a valid numeric string.');
        }

        $validTypes = ['credit', 'debit'];
        if (!in_array($this->type, $validTypes, true)) {
            throw new \InvalidArgumentException('Invalid transaction type.');
        }
    }
}