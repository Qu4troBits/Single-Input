<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Ports\UserAuthRepositoryInterface;
use App\Application\Auth\UserAuth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class EloquentUserAuthRepository implements UserAuthRepositoryInterface
{
    public function findByEmailAndTenant(string $email, int $tenantId): ?UserAuth
    {
        DB::statement('SET search_path TO public');

        $user = User::query()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($user === null) {
            return null;
        }

        return $this->map($user);
    }

    public function findById(int $userId): ?UserAuth
    {
        DB::statement('SET search_path TO public');

        $user = User::query()->whereKey($userId)->first();

        if ($user === null) {
            return null;
        }

        return $this->map($user);
    }

    public function saveTwoFactorSecret(int $userId, string $encryptedSecret): void
    {
        DB::statement('SET search_path TO public');

        User::query()
            ->whereKey($userId)
            ->update([
                'two_factor_secret' => $encryptedSecret,
            ]);
    }

    public function confirmTwoFactor(int $userId): void
    {
        DB::statement('SET search_path TO public');

        User::query()
            ->whereKey($userId)
            ->update([
                'two_factor_confirmed_at' => now(),
            ]);
    }

    private function map(User $user): UserAuth
    {
        $confirmedAt = $user->getAttribute('two_factor_confirmed_at');

        return new UserAuth(
            id: (int) $user->getAttribute('id'),
            tenantId: (int) $user->getAttribute('tenant_id'),
            passwordHash: (string) $user->getAttribute('password'),
            twoFactorSecretEncrypted: $user->getAttribute('two_factor_secret'),
            twoFactorConfirmedAt: $confirmedAt !== null ? (string) $confirmedAt : null,
        );
    }
}
