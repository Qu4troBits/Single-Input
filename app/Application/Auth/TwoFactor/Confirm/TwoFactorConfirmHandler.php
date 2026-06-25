<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Confirm;

use App\Application\Auth\Ports\TwoFactorSecretCipherInterface;
use App\Application\Auth\Ports\UserAuthRepositoryInterface;
use App\Domain\Security\TwoFactor\Totp;
use App\Domain\Tenancy\TenantContextInterface;
use RuntimeException;

final readonly class TwoFactorConfirmHandler
{
    public function __construct(
        private TenantContextInterface $tenantContext,
        private UserAuthRepositoryInterface $users,
        private TwoFactorSecretCipherInterface $cipher,
    ) {
    }

    public function handle(TwoFactorConfirmData $data): void
    {
        $user = $this->users->findById($data->userId);

        if ($user === null || $user->twoFactorSecretEncrypted === null) {
            throw new RuntimeException('Two-factor is not configured.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null || $tenant->id !== $user->tenantId) {
            throw new RuntimeException('Invalid tenant context.');
        }

        $secret = $this->cipher->decrypt($user->twoFactorSecretEncrypted);

        if (! Totp::verify($secret, $data->code)) {
            throw new RuntimeException('Invalid two-factor code.');
        }

        $this->users->confirmTwoFactor($data->userId);
    }
}
