<?php

declare(strict_types=1);

namespace App\Application\BankReconciliation\DTOs;

use DateTimeImmutable;

final readonly class BankStatementItemData
{
    public function __construct(
        public string $id,
        public DateTimeImmutable $date,
        public string $description,
        public string $amount,
        public string $type,
        public ?string $bankReference = null,
        public ?string $notes = null,
    ) {}
}
