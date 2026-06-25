<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Handlers;

use App\Application\BankAccounts\Data\UpdateBankAccountData;
use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountRepositoryInterface;
use RuntimeException;

final readonly class UpdateBankAccountHandler
{
    public function __construct(
        private BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function handle(BankAccountId $id, UpdateBankAccountData $data): void
    {
        $bankAccount = $this->bankAccountRepository->findById($id);

        if ($bankAccount === null) {
            throw new RuntimeException('Bank account not found.');
        }

        $bankAccount->update(
            name: $data->name,
            type: $data->type,
            status: $data->status,
            bankCode: $data->bankCode,
            agency: $data->agency,
            accountNumber: $data->accountNumber,
            accountDigit: $data->accountDigit,
            description: $data->description,
        );

        $this->bankAccountRepository->save($bankAccount);
    }
}