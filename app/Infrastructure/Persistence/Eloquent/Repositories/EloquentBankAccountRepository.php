<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Banking\BankAccount;
use App\Domain\Banking\BankAccountRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Tenant\BankAccountModel;

final readonly class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function listAll(): array
    {
        $rows = BankAccountModel::query()->orderBy('name')->get();
        $items = [];

        foreach ($rows as $row) {
            $items[] = new BankAccount(
                id: (int) $row->getAttribute('id'),
                name: (string) $row->getAttribute('name'),
            );
        }

        return $items;
    }
}
