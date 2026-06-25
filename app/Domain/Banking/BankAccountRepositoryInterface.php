<?php

declare(strict_types=1);

namespace App\Domain\Banking;

interface BankAccountRepositoryInterface
{
    /**
     * @return list<BankAccount>
     */
    public function listAll(): array;
}
