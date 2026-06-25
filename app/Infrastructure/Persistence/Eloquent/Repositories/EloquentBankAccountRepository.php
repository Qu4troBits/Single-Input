<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\BankAccounts\BankAccount;
use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\BankAccountStatus;
use App\Domain\BankAccounts\BankAccountType;
use App\Domain\Shared\Money;
use App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

final class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function save(BankAccount $bankAccount): void
    {
        BankAccountModel::updateOrCreate(
            ['id' => $bankAccount->getId()->toString()],
            [
                'name' => $bankAccount->getName(),
                'type' => $bankAccount->getType()->value,
                'status' => $bankAccount->getStatus()->value,
                'bank_code' => $bankAccount->getBankCode(),
                'agency' => $bankAccount->getAgency(),
                'account_number' => $bankAccount->getAccountNumber(),
                'account_digit' => $bankAccount->getAccountDigit(),
                'description' => $bankAccount->getDescription(),
                'balance' => $bankAccount->getBalance()->toNumeric(),
                'initial_balance' => $bankAccount->getInitialBalance()->toNumeric(),
                'created_at' => $bankAccount->getCreatedAt(),
                'updated_at' => $bankAccount->getUpdatedAt(),
            ]
        );
    }

    public function findById(BankAccountId $id): ?BankAccount
    {
        try {
            $model = BankAccountModel::findOrFail($id->toString());
            return $this->toDomain($model);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /** @return array<BankAccount> */
    public function findAll(): array
    {
        return BankAccountModel::all()
            ->map(fn (BankAccountModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<BankAccount> */
    public function findByStatus(BankAccountStatus $status): array
    {
        return BankAccountModel::where('status', $status->value)
            ->get()
            ->map(fn (BankAccountModel $model) => $this->toDomain($model))
            ->toArray();
    }

    /** @return array<BankAccount> */
    public function findByType(BankAccountType $type): array
    {
        return BankAccountModel::where('type', $type->value)
            ->get()
            ->map(fn (BankAccountModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function delete(BankAccountId $id): void
    {
        BankAccountModel::where('id', $id->toString())->delete();
    }

    private function toDomain(BankAccountModel $model): BankAccount
    {
        return new BankAccount(
            id: BankAccountId::fromString($model->id),
            name: $model->name,
            type: BankAccountType::from($model->type),
            status: BankAccountStatus::from($model->status),
            bankCode: $model->bank_code,
            agency: $model->agency,
            accountNumber: $model->account_number,
            accountDigit: $model->account_digit,
            description: $model->description,
            initialBalance: Money::of($model->initial_balance),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}