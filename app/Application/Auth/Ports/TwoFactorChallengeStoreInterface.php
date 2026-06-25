<?php

declare(strict_types=1);

namespace App\Application\Auth\Ports;

interface TwoFactorChallengeStoreInterface
{
    public function setPendingUserId(int $userId): void;

    public function pendingUserId(): ?int;

    public function clear(): void;
}
