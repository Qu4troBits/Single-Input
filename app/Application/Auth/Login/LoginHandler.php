<?php

declare(strict_types=1);

namespace App\Application\Auth\Login;

use App\Application\Auth\Ports\PasswordVerifierInterface;
use App\Application\Auth\Ports\SessionAuthenticatorInterface;
use App\Application\Auth\Ports\TwoFactorChallengeStoreInterface;
use App\Application\Auth\Ports\UserAuthRepositoryInterface;
use App\Domain\Tenancy\TenantContextInterface;
use RuntimeException; 

final readonly class LoginHandler
{
    public function __construct(
        private TenantContextInterface $tenantContext,
        private UserAuthRepositoryInterface $users,
        private PasswordVerifierInterface $passwords,
        private SessionAuthenticatorInterface $authenticator,
        private TwoFactorChallengeStoreInterface $twoFactorStore,
    ) {
    }

    public function handle(LoginData $data): LoginResult
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw new RuntimeException('Tenant context not configured.');
        }

        $user = $this->users->findByEmailAndTenant($data->email, intval($tenant->id));

        if ($user === null) {
            throw new RuntimeException('Invalid credentials.');
        }

        if (! $this->passwords->verify($data->password, $user->passwordHash)) {
            throw new RuntimeException('Invalid credentials.');
        }

        if ($user->twoFactorEnabled()) {
            $this->twoFactorStore->setPendingUserId($user->id);

            return new LoginResult(twoFactorRequired: true);
        }

        $this->twoFactorStore->clear();
        $this->authenticator->loginById($user->id);

        return new LoginResult(twoFactorRequired: false);
    }
}
