<?php

declare(strict_types=1);

namespace App\Application\Auth\Ports;

use App\Application\Auth\UserAuth;

interface UserAuthRepositoryInterface
{
    public function findByEmailAndTenant(string $email, int $tenantId): ?UserAuth;

    public function findById(int $userId): ?UserAuth;

    public function saveTwoFactorSecret(int $userId, string $encryptedSecret): void;

    public function confirmTwoFactor(int $userId): void;
}
