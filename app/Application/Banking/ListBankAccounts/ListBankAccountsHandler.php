<?php

declare(strict_types=1);

namespace App\Application\Banking\ListBankAccounts;

use App\Domain\Banking\BankAccountRepositoryInterface;

final readonly class ListBankAccountsHandler
{
    public function __construct(private BankAccountRepositoryInterface $bankAccounts)
    {
    }

    public function handle(): array
    {
        return $this->bankAccounts->listAll();
    }
}
