<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Challenge;

use App\Application\Auth\Ports\SessionAuthenticatorInterface;
use App\Application\Auth\Ports\TwoFactorChallengeStoreInterface;
use App\Application\Auth\Ports\TwoFactorSecretCipherInterface;
use App\Application\Auth\Ports\UserAuthRepositoryInterface;
use App\Domain\Security\TwoFactor\Totp;
use App\Domain\Tenancy\TenantContextInterface;
use RuntimeException;

final readonly class TwoFactorChallengeHandler
{
    public function __construct(
        private TenantContextInterface $tenantContext,
        private UserAuthRepositoryInterface $users,
        private TwoFactorChallengeStoreInterface $store,
        private TwoFactorSecretCipherInterface $cipher,
        private SessionAuthenticatorInterface $authenticator,
    ) {
    }

    public function handle(TwoFactorChallengeData $data): void
    {
        $userId = $this->store->pendingUserId();

        if ($userId === null) {
            throw new RuntimeException('Two-factor challenge is not pending.');
        }

        $user = $this->users->findById($userId);

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

        $this->store->clear();
        $this->authenticator->loginById($userId);
    }
}
