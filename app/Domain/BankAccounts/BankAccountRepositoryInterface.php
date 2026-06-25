<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts;

interface BankAccountRepositoryInterface
{
    public function save(BankAccount $bankAccount): void;

    public function findById(BankAccountId $id): ?BankAccount;

    /** @return array<BankAccount> */
    public function findAll(): array;

    /** @return array<BankAccount> */
    public function findByStatus(BankAccountStatus $status): array;

    /** @return array<BankAccount> */
    public function findByType(BankAccountType $type): array;

    public function delete(BankAccountId $id): void;
}