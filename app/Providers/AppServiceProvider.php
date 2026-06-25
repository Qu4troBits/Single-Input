<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Auth\Ports\PasswordVerifierInterface;
use App\Application\Auth\Ports\SessionAuthenticatorInterface;
use App\Application\Auth\Ports\TwoFactorChallengeStoreInterface;
use App\Application\Auth\Ports\TwoFactorSecretCipherInterface;
use App\Application\Auth\Ports\UserAuthRepositoryInterface;
use App\Application\Tenancy\Ports\InitialTenantAdminCreatorInterface;
use App\Application\Tenancy\Ports\TenantSchemaManagerInterface;
use App\Domain\Banking\BankAccountRepositoryInterface;
use App\Domain\Categories\CategoryRepositoryInterface;
use App\Domain\Plans\PlanRepositoryInterface;
use App\Domain\Tenancy\TenantContextInterface;
use App\Domain\Tenancy\TenantRepositoryInterface;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Infrastructure\Auth\BcryptPasswordVerifier;
use App\Infrastructure\Auth\EloquentUserAuthRepository;
use App\Infrastructure\Auth\LaravelSessionAuthenticator;
use App\Infrastructure\Auth\LaravelTwoFactorChallengeStore;
use App\Infrastructure\Auth\LaravelTwoFactorSecretCipher;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPlanRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentBankAccountRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTransactionRepository;
use App\Infrastructure\Tenancy\InitialTenantAdminCreator;
use App\Infrastructure\Tenancy\TenantContext;
use App\Infrastructure\Tenancy\TenantSchemaManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContextInterface::class, TenantContext::class);

        $this->app->bind(TenantRepositoryInterface::class, EloquentTenantRepository::class);
        $this->app->bind(PlanRepositoryInterface::class, EloquentPlanRepository::class);

        $this->app->bind(TenantSchemaManagerInterface::class, TenantSchemaManager::class);
        $this->app->bind(InitialTenantAdminCreatorInterface::class, InitialTenantAdminCreator::class);

        $this->app->bind(UserAuthRepositoryInterface::class, EloquentUserAuthRepository::class);
        $this->app->bind(PasswordVerifierInterface::class, BcryptPasswordVerifier::class);
        $this->app->bind(SessionAuthenticatorInterface::class, LaravelSessionAuthenticator::class);
        $this->app->bind(TwoFactorSecretCipherInterface::class, LaravelTwoFactorSecretCipher::class);
        $this->app->bind(TwoFactorChallengeStoreInterface::class, LaravelTwoFactorChallengeStore::class);

        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(BankAccountRepositoryInterface::class, EloquentBankAccountRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
