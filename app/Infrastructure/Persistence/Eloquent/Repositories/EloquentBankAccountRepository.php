<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function findById(BankAccountId $id): ?BankAccount
    {
        $model = BankAccountModel::find($id->toString());

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function findByAccountNumber(string $bankCode, string $agencyNumber, string $accountNumber): ?BankAccount
    {
        $model = BankAccountModel::where('bank_code', $bankCode)
            ->where('agency_number', $agencyNumber)
            ->where('account_number', $accountNumber)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function findAll(
        ?BankAccountType $type = null,
        ?BankAccountStatus $status = null,
        bool $includeInDashboard = false,
        bool $includeInReports = false,
        bool $isDefault = false,
        int $page = 1,
        int $perPage = 20
    ): array {
        $query = BankAccountModel::query();

        if ($type) {
            $query->where('type', $type->value);
        }

        if ($status) {
            $query->where('status', $status->value);
        }

        if ($includeInDashboard) {
            $query->where('include_in_dashboard', true);
        }

        if ($includeInReports) {
            $query->where('include_in_reports', true);
        }

        if ($isDefault) {
            $query->where('is_default', true);
        }

        $query->orderBy('name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $this->mapCollectionToEntities($paginator->items()),
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    public function findAllActive(): array
    {
        $models = BankAccountModel::where('status', BankAccountStatus::ACTIVE->value)
            ->orderBy('name')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function findAllForDashboard(): array
    {
        $models = BankAccountModel::where('status', BankAccountStatus::ACTIVE->value)
            ->where('include_in_dashboard', true)
            ->orderBy('name')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function findAllForReports(): array
    {
        $models = BankAccountModel::where('status', BankAccountStatus::ACTIVE->value)
            ->where('include_in_reports', true)
            ->orderBy('name')
            ->get();

        return $this->mapCollectionToEntities($models->all());
    }

    public function getDefaultAccount(): ?BankAccount
    {
        $model = BankAccountModel::where('is_default', true)->first();

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function save(BankAccount $bankAccount): void
    {
        $data = [
            'id' => $bankAccount->getId()->toString(),
            'name' => $bankAccount->getName(),
            'type' => $bankAccount->getType()->value,
            'bank_code' => $bankAccount->getBankCode(),
            'bank_name' => $bankAccount->getBankName(),
            'agency_number' => $bankAccount->getAgencyNumber(),
            'account_number' => $bankAccount->getAccountNumber(),
            'account_digit' => $bankAccount->getAccountDigit(),
            'initial_balance' => $bankAccount->getInitialBalance()->getAmount(),
            'current_balance' => $bankAccount->getCurrentBalance()->getAmount(),
            'status' => $bankAccount->getStatus()->value,
            'description' => $bankAccount->getDescription(),
            'color' => $bankAccount->getColor(),
            'icon' => $bankAccount->getIcon(),
            'include_in_dashboard' => $bankAccount->isIncludeInDashboard(),
            'include_in_reports' => $bankAccount->isIncludeInReports(),
            'is_default' => $bankAccount->isDefault(),
            'created_at' => $bankAccount->getCreatedAt(),
            'updated_at' => $bankAccount->getUpdatedAt(),
        ];

        if ($bankAccount->getDeletedAt()) {
            $data['deleted_at'] = $bankAccount->getDeletedAt();
        }

        BankAccountModel::updateOrCreate(['id' => $bankAccount->getId()->toString()], $data);
    }

    public function delete(BankAccount $bankAccount): void
    {
        $model = BankAccountModel::find($bankAccount->getId()->toString());

        if ($model) {
            $model->delete();
        }
    }

    public function updateBalance(BankAccountId $id, Money $newBalance): void
    {
        BankAccountModel::where('id', $id->toString())
            ->update([
                'current_balance' => $newBalance->getAmount(),
                'updated_at' => new DateTimeImmutable(),
            ]);
    }

    public function existsWithAccountNumber(
        string $bankCode,
        string $agencyNumber,
        string $accountNumber,
        ?BankAccountId $excludeId = null
    ): bool {
        $query = BankAccountModel::where('bank_code', $bankCode)
            ->where('agency_number', $agencyNumber)
            ->where('account_number', $accountNumber);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId->toString());
        }

        return $query->exists();
    }

    public function countByType(BankAccountType $type): int
    {
        return BankAccountModel::where('type', $type->value)->count();
    }

    public function countByStatus(BankAccountStatus $status): int
    {
        return BankAccountModel::where('status', $status->value)->count();
    }

    public function getTotalBalance(): Money
    {
        $total = BankAccountModel::where('status', BankAccountStatus::ACTIVE->value)
            ->sum('current_balance');

        return Money::of($total);
    }

    public function getTotalBalanceByType(BankAccountType $type): Money
    {
        $total = BankAccountModel::where('type', $type->value)
            ->where('status', BankAccountStatus::ACTIVE->value)
            ->sum('current_balance');

        return Money::of($total);
    }

    private function mapToEntity(BankAccountModel $model): BankAccount
    {
        return new BankAccount(
            id: BankAccountId::fromString($model->id),
            name: $model->name,
            type: BankAccountType::from($model->type),
            bankCode: $model->bank_code,
            bankName: $model->bank_name,
            agencyNumber: $model->agency_number,
            accountNumber: $model->account_number,
            accountDigit: $model->account_digit,
            initialBalance: Money::of($model->initial_balance),
            currentBalance: Money::of($model->current_balance),
            status: BankAccountStatus::from($model->status),
            description: $model->description,
            color: $model->color,
            icon: $model->icon,
            includeInDashboard: $model->include_in_dashboard,
            includeInReports: $model->include_in_reports,
            isDefault: $model->is_default,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at),
        );
    }

    private function mapCollectionToEntities(array $models): array
    {
        return array_map(
            fn (BankAccountModel $model) => $this->mapToEntity($model),
            $models
        );
    }
}
