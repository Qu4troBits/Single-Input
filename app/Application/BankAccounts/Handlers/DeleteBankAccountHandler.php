<?php

declare(strict_types=1);

namespace App\Application\BankAccounts\Handlers;

use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use DateTimeImmutable;

final class DeleteBankAccountHandler
{
    public function __construct(
        private readonly BankAccountRepositoryInterface $bankAccountRepository,
    ) {}

    public function handle(BankAccountId $id): void
    {
        $bankAccount = $this->bankAccountRepository->findById($id);
        if (!$bankAccount) {
            throw new \DomainException('Conta bancária não encontrada.');
        }

        // Verificar se a conta está ativa
        if ($bankAccount->getStatus() === BankAccountStatus::ACTIVE) {
            throw new \DomainException('Não é possível excluir uma conta bancária ativa. Desative-a primeiro.');
        }

        // Verificar se existem transações associadas à conta
        if ($this->bankAccountRepository->hasTransactions($id)) {
            throw new \DomainException('Não é possível excluir uma conta bancária com transações associadas.');
        }

        // Verificar se é a conta padrão
        if ($bankAccount->isDefault()) {
            throw new \DomainException('Não é possível excluir a conta bancária padrão.');
        }

        $this->bankAccountRepository->delete($id);
    }

    public function deactivate(BankAccountId $id): void
    {
        $bankAccount = $this->bankAccountRepository->findById($id);
        if (!$bankAccount) {
            throw new \DomainException('Conta bancária não encontrada.');
        }

        // Verificar se já está inativa
        if ($bankAccount->getStatus() === BankAccountStatus::INACTIVE) {
            throw new \DomainException('A conta bancária já está inativa.');
        }

        // Verificar se é a conta padrão
        if ($bankAccount->isDefault()) {
            throw new \DomainException('Não é possível desativar a conta bancária padrão.');
        }

        $bankAccount->changeStatus(BankAccountStatus::INACTIVE, new DateTimeImmutable());
        $this->bankAccountRepository->save($bankAccount);
    }

    public function activate(BankAccountId $id): void
    {
        $bankAccount = $this->bankAccountRepository->findById($id);
        if (!$bankAccount) {
            throw new \DomainException('Conta bancária não encontrada.');
        }

        // Verificar se já está ativa
        if ($bankAccount->getStatus() === BankAccountStatus::ACTIVE) {
            throw new \DomainException('A conta bancária já está ativa.');
        }

        $bankAccount->changeStatus(BankAccountStatus::ACTIVE, new DateTimeImmutable());
        $this->bankAccountRepository->save($bankAccount);
    }
}