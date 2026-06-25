<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Finance\Money;
use App\Domain\Transactions\Transaction;
use App\Domain\Transactions\TransactionDirection;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Domain\Transactions\TransactionStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Tenant\TransactionModel;
use RuntimeException;

final readonly class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function listLatest(int $limit = 50): array
    {
        $rows = TransactionModel::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->map($row);
        }

        return $items;
    }

    public function create(
        int $bankAccountId,
        int $categoryId,
        string $description,
        Money $amount,
        TransactionDirection $direction,
        TransactionStatus $status,
        string $competenceMonth,
        ?string $paymentDate,
    ): Transaction {
        $row = TransactionModel::query()->create([
            'bank_account_id' => $bankAccountId,
            'category_id' => $categoryId,
            'description' => $description,
            'amount' => $amount->toString(),
            'direction' => $direction->value,
            'status' => $status->value,
            'competence_month' => $competenceMonth,
            'payment_date' => $paymentDate,
        ]);

        return $this->map($row);
    }

    private function map(TransactionModel $row): Transaction
    {
        $direction = TransactionDirection::tryFrom((string) $row->getAttribute('direction'));
        $status = TransactionStatus::tryFrom((string) $row->getAttribute('status'));

        if ($direction === null || $status === null) {
            throw new RuntimeException('Invalid transaction record.');
        }

        $paymentDate = $row->getAttribute('payment_date');

        return new Transaction(
            id: (int) $row->getAttribute('id'),
            bankAccountId: (int) $row->getAttribute('bank_account_id'),
            categoryId: (int) $row->getAttribute('category_id'),
            description: (string) $row->getAttribute('description'),
            amount: Money::of((string) $row->getAttribute('amount')),
            direction: $direction,
            status: $status,
            competenceMonth: (string) $row->getAttribute('competence_month'),
            paymentDate: $paymentDate !== null ? (string) $paymentDate : null,
        );
    }
}
