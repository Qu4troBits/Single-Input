<?php

declare(strict_types=1); 

namespace App\Domain\Transactions;

use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Shared\Money;
use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionStatus;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;

    public function findById(TransactionId $id): ?Transaction;

    /** @return array<Transaction> */
    public function findAll(): array;

    /** @return array<Transaction> */
    public function findByBankAccountId(BankAccountId $bankAccountId): array;

    /** @return array<Transaction> */
    public function findByCategoryId(CategoryId $categoryId): array;

    /** @return array<Transaction> */
    public function findByStatus(TransactionStatus $status): array;

    /** @return array<Transaction> */
    public function findByCompetenceMonth(string $competenceMonth): array;

    /** @return array<Transaction> */
    public function findByPeriod(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    /** @return array<Transaction> */
    public function findPendingByBankAccountId(BankAccountId $bankAccountId): array;

    public function delete(TransactionId $id): void;

    public function getBalanceForBankAccount(BankAccountId $bankAccountId): Money;

    public function listLatest(int $limit = 50): array;
}