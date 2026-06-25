<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Handlers;

use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountRepositoryInterface;
use RuntimeException;

final readonly class DeleteBankAccountHandler
{
    public function __construct(
        private BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function handle(BankAccountId $id): void
    {
        $bankAccount = $this->bankAccountRepository->findById($id);

        if ($bankAccount === null) {
            throw new RuntimeException('Bank account not found.');
        }

        $this->bankAccountRepository->delete($id);
    }
}