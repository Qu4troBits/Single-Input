<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Handlers;

use App\Application\BankAccounts\DTOs\CreateBankAccountData;
use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use DateTimeImmutable;

final class CreateBankAccountHandler
{
    public function __construct(
        private readonly BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function handle(CreateBankAccountData $data): BankAccount
    {
        // Verificar se já existe uma conta com o mesmo número
        if ($this->bankAccountRepository->existsWithAccountNumber(
            $data->bankCode,
            $data->agencyNumber,
            $data->accountNumber
        )) {
            throw new \DomainException('Já existe uma conta bancária com este número.');
        }

        // Se esta conta for marcada como padrão, desmarcar outras
        if ($data->isDefault) {
            $this->unsetOtherDefaultAccounts();
        }

        $now = new DateTimeImmutable();
        $bankAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: $data->name,
            type: $data->type,
            bankCode: $data->bankCode,
            bankName: $data->bankName,
            agencyNumber: $data->agencyNumber,
            accountNumber: $data->accountNumber,
            accountDigit: $data->accountDigit,
            initialBalance: $data->initialBalance,
            currentBalance: $data->initialBalance,
            status: BankAccountStatus::ACTIVE,
            description: $data->description,
            color: $data->color,
            icon: $data->icon,
            includeInDashboard: $data->includeInDashboard,
            includeInReports: $data->includeInReports,
            isDefault: $data->isDefault,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->bankAccountRepository->save($bankAccount);

        return $bankAccount;
    }

    private function unsetOtherDefaultAccounts(): void
    {
        $defaultAccount = $this->bankAccountRepository->getDefaultAccount();
        
        if ($defaultAccount) {
            $defaultAccount->update(
                name: $defaultAccount->getName(),
                type: $defaultAccount->getType(),
                bankCode: $defaultAccount->getBankCode(),
                bankName: $defaultAccount->getBankName(),
                agencyNumber: $defaultAccount->getAgencyNumber(),
                accountNumber: $defaultAccount->getAccountNumber(),
                accountDigit: $defaultAccount->getAccountDigit(),
                initialBalance: $defaultAccount->getInitialBalance(),
                description: $defaultAccount->getDescription(),
                color: $defaultAccount->getColor(),
                icon: $defaultAccount->getIcon(),
                includeInDashboard: $defaultAccount->isIncludeInDashboard(),
                includeInReports: $defaultAccount->isIncludeInReports(),
                isDefault: false,
                updatedAt: new DateTimeImmutable(),
            );
            
            $this->bankAccountRepository->save($defaultAccount);
        }
    }
}
