<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\DTOs;

use App\Domain\BankReconciliation\ReconciliationStatus;
use DateTimeImmutable;

final readonly class ReconciliationItemData
{
    public function __construct(
        public string $id,
        public string $description,
        public string $amount,
        public DateTimeImmutable $date,
        public ReconciliationStatus $status,
        public ?string $transactionId = null,
        public ?string $bankStatementId = null,
        public ?string $notes = null,
    ) {}
}
