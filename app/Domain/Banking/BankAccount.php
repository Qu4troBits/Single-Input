<?php

declare(strict_types=1);

namespace App\Domain\Banking;

final readonly class BankAccount
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
