<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\Data;

use App\Domain\BankReconciliation\ReconciliationStatus;

final readonly class ReconcileBankAccountData
{
    /**
     * @param array<ReconciliationItemData> $items
     */
    public function __construct(
        public string $bankAccountId,
        public \DateTimeImmutable $reconciliationDate,
        public array $items,
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
            throw new \InvalidArgumentException('At least one reconciliation item is required.');
        }

        foreach ($this->items as $item) {
            if (!$item instanceof ReconciliationItemData) {
                throw new \InvalidArgumentException('All items must be instances of ReconciliationItemData.');
            }
        }
    }
}

final readonly class ReconciliationItemData
{
    public function __construct(
        public string $id,
        public string $description,
        public string $amount,
        public \DateTimeImmutable $date,
        public ReconciliationStatus $status,
        public ?string $transactionId = null,
        public ?string $bankStatementId = null,
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
    }
}