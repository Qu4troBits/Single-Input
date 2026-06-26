<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\BankAccounts\BankAccountId;
use App\Domain\Categories\CategoryId;
use App\Domain\Shared\Money;
use App\Domain\Transactions\Transaction;
use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Domain\Transactions\TransactionStatus;
use App\Infrastructure\Persistence\Eloquent\Models\TransactionModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

final class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void
    {
        TransactionModel::updateOrCreate(
            ['id' => $transaction->getId()->toString()],
            [
                'bank_account_id' => $transaction->getBankAccountId()->toString(),
                'category_id' => $transaction->getCategoryId()->toString(),
                'description' => $transaction->getDescription(),
                'amount' => $transaction->getAmount()->toNumeric(),
                'direction' => $transaction->getDirection()->value,
                'status' => $transaction->getStatus()->value,
                'competence_month' => $transaction->getCompetenceMonth(),
                'payment_date' => $transaction->getPaymentDate(),
                'created_at' => $transaction->getCreatedAt(),
                'updated_at' => $transaction->getUpdatedAt(),
            ]
        );
    }

    public function findById(TransactionId $id): ?Transaction
    {
        try {
            $model = TransactionModel::findOrFail($id->toString());
            return $this->toDomain($model);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /** @return array<Transaction> */
    public function findAll(): array
    {
        return TransactionModel::all()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Transaction> */
    public function findByBankAccountId(BankAccountId $bankAccountId): array
    {
        return TransactionModel::where('bank_account_id', $bankAccountId->toString())
            ->get()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Transaction> */
    public function findByCategoryId(CategoryId $categoryId): array
    {
        return TransactionModel::where('category_id', $categoryId->toString())
            ->get()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Transaction> */
    public function findByStatus(TransactionStatus $status): array
    {
        return TransactionModel::where('status', $status->value)
            ->get()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Transaction> */
    public function findByCompetenceMonth(string $competenceMonth): array
    {
        return TransactionModel::where('competence_month', $competenceMonth)
            ->get()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Transaction> */
    public function findByPeriod(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return TransactionModel::whereBetween('payment_date', [$startDate, $endDate])
            ->get()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<Transaction> */
    public function findPendingByBankAccountId(BankAccountId $bankAccountId): array
    {
        return TransactionModel::where('bank_account_id', $bankAccountId->toString())
            ->where('status', TransactionStatus::PENDING->value)
            ->get()
            ->map(fn (TransactionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function delete(TransactionId $id): void
    {
        TransactionModel::where('id', $id->toString())->delete();
    }

    public function getBalanceForBankAccount(BankAccountId $bankAccountId): Money
    {
        $income = TransactionModel::where('bank_account_id', $bankAccountId->toString())
            ->where('status', TransactionStatus::PAID->value)
            ->where('direction', 'in')
            ->sum('amount');

        $expense = TransactionModel::where('bank_account_id', $bankAccountId->toString())
            ->where('status', TransactionStatus::PAID->value)
            ->where('direction', 'out')
            ->sum('amount');

        $balance = bcsub($income, $expense, 2);
        return Money::of($balance);
    }

    private function toDomain(TransactionModel $model): Transaction
    {
        return new Transaction(
            id: TransactionId::fromString($model->id),
            bankAccountId: BankAccountId::fromString($model->bank_account_id),
            categoryId: CategoryId::fromString($model->category_id),
            description: $model->description,
            amount: Money::of($model->amount),
            direction: \App\Domain\Transactions\TransactionDirection::from($model->direction),
            status: TransactionStatus::from($model->status),
            competenceMonth: $model->competence_month,
            paymentDate: $model->payment_date,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}