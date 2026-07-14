<?php

declare(strict_types=1);

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;
use App\Models\User;

final class BankAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('bank_accounts.view_any');
    }

    public function view(User $user, BankAccountModel $bankAccount): bool
    {
        return $user->hasPermission('bank_accounts.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('bank_accounts.create');
    }

    public function update(User $user, BankAccountModel $bankAccount): bool
    {
        return $user->hasPermission('bank_accounts.update');
    }

    public function delete(User $user, BankAccountModel $bankAccount): bool
    {
        return $user->hasPermission('bank_accounts.delete');
    }

    public function restore(User $user, BankAccountModel $bankAccount): bool
    {
        return $user->hasPermission('bank_accounts.restore');
    }

    public function forceDelete(User $user, BankAccountModel $bankAccount): bool
    {
        return $user->hasPermission('bank_accounts.force_delete');
    }
}
