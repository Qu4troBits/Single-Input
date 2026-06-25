<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Handlers;

use App\Application\BankAccounts\Data\CreateBankAccountData;
use App\Domain\BankAccounts\BankAccount;
use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\BankAccountStatus;
use App\Domain\BankAccounts\BankAccountType;

final readonly class CreateBankAccountHandler
{
    public function __construct(
        private BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function handle(CreateBankAccountData $data): BankAccountId
    {
        $bankAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: $data->name,
            type: $data->type,
            status: BankAccountStatus::ACTIVE,
            bankCode: $data->bankCode,
            agency: $data->agency,
            accountNumber: $data->accountNumber,
            accountDigit: $data->accountDigit,
            description: $data->description,
            initialBalance: $data->initialBalance,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->bankAccountRepository->save($bankAccount);

        return $bankAccount->getId();
    }
}