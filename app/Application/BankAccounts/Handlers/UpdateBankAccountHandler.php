<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Handlers;

use App\Application\BankAccounts\DTOs\UpdateBankAccountData;
use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use DateTimeImmutable;

final class UpdateBankAccountHandler
{
    public function __construct(
        private readonly BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function handle(UpdateBankAccountData $data): BankAccount
    {
        $bankAccount = $this->bankAccountRepository->findById($data->id);
        if (!$bankAccount) {
            throw new \DomainException('Conta bancária não encontrada.');
        }

        // Verificar se já existe outra conta com o mesmo número (excluindo a atual)
        $existingAccount = $this->bankAccountRepository->findByAccountNumber(
            $data->bankCode,
            $data->agencyNumber,
            $data->accountNumber
        );
        
        if ($existingAccount && !$existingAccount->getId()->equals($data->id)) {
            throw new \DomainException('Já existe outra conta bancária com este número.');
        }

        // Se esta conta for marcada como padrão, desmarcar outras
        if ($data->isDefault && !$bankAccount->isDefault()) {
            $this->unsetOtherDefaultAccounts($data->id);
        }

        // Atualizar saldo atual se o saldo inicial mudou
        $currentBalance = $bankAccount->getCurrentBalance();
        if (!$bankAccount->getInitialBalance()->equals($data->initialBalance)) {
            $difference = $data->initialBalance->subtract($bankAccount->getInitialBalance());
            $currentBalance = $currentBalance->add($difference);
        }

        $updatedBankAccount = new BankAccount(
            id: $data->id,
            name: $data->name,
            type: $data->type,
            bankCode: $data->bankCode,
            bankName: $data->bankName,
            agencyNumber: $data->agencyNumber,
            accountNumber: $data->accountNumber,
            accountDigit: $data->accountDigit,
            initialBalance: $data->initialBalance,
            currentBalance: $currentBalance,
            status: $bankAccount->getStatus(),
            description: $data->description,
            color: $data->color,
            icon: $data->icon,
            includeInDashboard: $data->includeInDashboard,
            includeInReports: $data->includeInReports,
            isDefault: $data->isDefault,
            createdAt: $bankAccount->getCreatedAt(),
            updatedAt: new DateTimeImmutable(),
        );

        $this->bankAccountRepository->save($updatedBankAccount);

        return $updatedBankAccount;
    }

    private function unsetOtherDefaultAccounts(BankAccountId $excludeId): void
    {
        $defaultAccounts = $this->bankAccountRepository->findAll(
            isDefault: true,
            status: BankAccountStatus::ACTIVE
        );

        foreach ($defaultAccounts['data'] as $account) {
            if (!$account->getId()->equals($excludeId)) {
                $updatedAccount = new BankAccount(
                    id: $account->getId(),
                    name: $account->getName(),
                    type: $account->getType(),
                    bankCode: $account->getBankCode(),
                    bankName: $account->getBankName(),
                    agencyNumber: $account->getAgencyNumber(),
                    accountNumber: $account->getAccountNumber(),
                    accountDigit: $account->getAccountDigit(),
                    initialBalance: $account->getInitialBalance(),
                    currentBalance: $account->getCurrentBalance(),
                    status: $account->getStatus(),
                    description: $account->getDescription(),
                    color: $account->getColor(),
                    icon: $account->getIcon(),
                    includeInDashboard: $account->isIncludeInDashboard(),
                    includeInReports: $account->isIncludeInReports(),
                    isDefault: false,
                    createdAt: $account->getCreatedAt(),
                    updatedAt: new DateTimeImmutable(),
                );

                $this->bankAccountRepository->save($updatedAccount);
            }
        }
    }
}