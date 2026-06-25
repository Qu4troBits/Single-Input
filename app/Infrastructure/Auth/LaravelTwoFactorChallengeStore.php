<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Ports\TwoFactorChallengeStoreInterface;

final readonly class LaravelTwoFactorChallengeStore implements TwoFactorChallengeStoreInterface
{
    private const KEY = 'auth.two_factor.pending_user_id';

    public function setPendingUserId(int $userId): void
    {
        session()->put(self::KEY, $userId);
    }

    public function pendingUserId(): ?int
    {
        $value = session()->get(self::KEY);

        if ($value === null) {
            return null;
        }

        return is_int($value) ? $value : (int) $value;
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }
}
