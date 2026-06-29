<?php

declare(strict_types=1);

namespace Tests\Unit\Application\BankAccounts;

use App\Application\BankAccounts\Handlers\DeleteBankAccountHandler;
use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeleteBankAccountHandlerTest extends TestCase
{
    private BankAccountRepositoryInterface $bankAccountRepository;
    private DeleteBankAccountHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bankAccountRepository = $this->createMock(BankAccountRepositoryInterface::class);
        $this->handler = new DeleteBankAccountHandler($this->bankAccountRepository);
    }

    /** @test */
    public function it_deletes_bank_account_successfully(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::INACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('hasTransactions')
            ->with($bankAccountId)
            ->willReturn(false);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('delete')
            ->with($bankAccountId);

        $this->handler->handle($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_bank_account_not_found(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Conta bancária não encontrada.');

        $this->handler->handle($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_bank_account_is_active(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::ACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir uma conta bancária ativa. Desative-a primeiro.');

        $this->handler->handle($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_bank_account_has_transactions(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::INACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('hasTransactions')
            ->with($bankAccountId)
            ->willReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir uma conta bancária com transações associadas.');

        $this->handler->handle($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_bank_account_is_default(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::INACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: true,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('hasTransactions')
            ->with($bankAccountId)
            ->willReturn(false);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir a conta bancária padrão.');

        $this->handler->handle($bankAccountId);
    }

    /** @test */
    public function it_deactivates_bank_account_successfully(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::ACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (BankAccount $updatedBankAccount) use ($bankAccountId) {
                return $updatedBankAccount->getId()->equals($bankAccountId)
                    && $updatedBankAccount->getStatus() === BankAccountStatus::INACTIVE;
            }));

        $this->handler->deactivate($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_deactivating_already_inactive_account(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::INACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A conta bancária já está inativa.');

        $this->handler->deactivate($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_deactivating_default_account(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::ACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: true,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível desativar a conta bancária padrão.');

        $this->handler->deactivate($bankAccountId);
    }

    /** @test */
    public function it_activates_bank_account_successfully(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::INACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (BankAccount $updatedBankAccount) use ($bankAccountId) {
                return $updatedBankAccount->getId()->equals($bankAccountId)
                    && $updatedBankAccount->getStatus() === BankAccountStatus::ACTIVE;
            }));

        $this->handler->activate($bankAccountId);
    }

    /** @test */
    public function it_throws_exception_when_activating_already_active_account(): void
    {
        $bankAccountId = BankAccountId::fromString('bank_12345678-1234-1234-1234-123456789012');
        $bankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Teste',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::ACTIVE,
            description: null,
            color: null,
            icon: null,
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($bankAccount);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A conta bancária já está ativa.');

        $this->handler->activate($bankAccountId);
    }
}