<?php

declare(strict_types=1);

namespace App\Application\Auth\TwoFactor\Setup;

use App\Application\Auth\Ports\TwoFactorSecretCipherInterface;
use App\Application\Auth\Ports\UserAuthRepositoryInterface;
use App\Domain\Security\TwoFactor\Totp;
use App\Domain\Tenancy\TenantContextInterface;
use RuntimeException;

final readonly class TwoFactorSetupHandler
{
    public function __construct(
        private TenantContextInterface $tenantContext,
        private UserAuthRepositoryInterface $users,
        private TwoFactorSecretCipherInterface $cipher,
    ) {
    }

    public function handle(TwoFactorSetupData $data): TwoFactorSetupResult
    {
        $user = $this->users->findById($data->userId);

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null || $tenant->id !== $user->tenantId) {
            throw new RuntimeException('Invalid tenant context.');
        }

        $secret = $user->twoFactorSecretEncrypted !== null
            ? $this->cipher->decrypt($user->twoFactorSecretEncrypted)
            : Totp::generateSecret();

        if ($user->twoFactorSecretEncrypted === null) {
            $this->users->saveTwoFactorSecret($data->userId, $this->cipher->encrypt($secret));
        }

        return new TwoFactorSetupResult(
            secret: $secret,
            otpAuthUri: Totp::otpAuthUri($data->issuer, $data->accountName, $secret),
        );
    }
}
