<?php

declare(strict_types=1);

namespace App\Application\Auth;

final readonly class UserAuth
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public string $passwordHash,
        public ?string $twoFactorSecretEncrypted,
        public ?string $twoFactorConfirmedAt,
    ) {
    }

    public function twoFactorEnabled(): bool
    {
        return $this->twoFactorSecretEncrypted !== null && $this->twoFactorConfirmedAt !== null;
    }
}
