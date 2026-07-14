<?php

declare(strict_types=1);

namespace Tests\Unit\Application\BankAccounts;

use App\Application\BankAccounts\DTOs\UpdateBankAccountData;
use App\Application\BankAccounts\Handlers\UpdateBankAccountHandler;
use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase; 
use PHPUnit\Framework\MockObject\MockObject;

final class UpdateBankAccountHandlerTest extends TestCase
{
    /** @var BankAccountRepositoryInterface&MockObject */
    private BankAccountRepositoryInterface&MockObject $bankAccountRepository;
    private UpdateBankAccountHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bankAccountRepository = $this->createMock(BankAccountRepositoryInterface::class);
        $this->handler = new UpdateBankAccountHandler($this->bankAccountRepository);
    }

    /** @test */
    public function it_updates_bank_account_successfully(): void
    {
        $bankAccountId = BankAccountId::generate();
        $existingBankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Antiga',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '56789',
            accountDigit: '0',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1500.00'),
            status: BankAccountStatus::ACTIVE,
            description: 'Descrição antiga',
            color: '#FF0000',
            icon: '🏦',
            includeInDashboard: true,
            includeInReports: true,
            isDefault: false,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $updateData = new UpdateBankAccountData(
            id: $bankAccountId,
            name: 'Conta Nova',
            type: BankAccountType::SAVINGS,
            bankCode: '033',
            bankName: 'Santander',
            agencyNumber: '4321',
            accountNumber: '98765',
            accountDigit: '1',
            initialBalance: Money::of('2000.00'),
            description: 'Descrição nova',
            color: '#00FF00',
            icon: '💰',
            includeInDashboard: false,
            includeInReports: false,
            isDefault: true,
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($existingBankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findByAccountNumber')
            ->with('033', '4321', '98765')
            ->willReturn(null);

        $this->bankAccountRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn(['data' => []]);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (BankAccount $bankAccount) use ($bankAccountId) {
                return $bankAccount->getId()->equals($bankAccountId)
                    && $bankAccount->getName() === 'Conta Nova'
                    && $bankAccount->getType() === BankAccountType::SAVINGS
                    && $bankAccount->getBankCode() === '033'
                    && $bankAccount->getBankName() === 'Santander'
                    && $bankAccount->getAgencyNumber() === '4321'
                    && $bankAccount->getAccountNumber() === '98765'
                    && $bankAccount->getAccountDigit() === '1'
                    && $bankAccount->getInitialBalance()->equals(Money::of('2000.00'))
                    && $bankAccount->getCurrentBalance()->equals(Money::of('2500.00')) // 1500 + (2000-1000)
                    && $bankAccount->getStatus() === BankAccountStatus::ACTIVE
                    && $bankAccount->getDescription() === 'Descrição nova'
                    && $bankAccount->getColor() === '#00FF00'
                    && $bankAccount->getIcon() === '💰'
                    && $bankAccount->isIncludeInDashboard() === false
                    && $bankAccount->isIncludeInReports() === false
                    && $bankAccount->isDefault() === true;
            }));

        $result = $this->handler->handle($updateData);

        $this->assertInstanceOf(BankAccount::class, $result);
        $this->assertTrue($result->getId()->equals($bankAccountId));
    }

    /** @test */
    public function it_throws_exception_when_bank_account_not_found(): void
    {
        $bankAccountId = BankAccountId::generate();
        $updateData = new UpdateBankAccountData(
            id: $bankAccountId,
            name: 'Conta Nova',
            type: BankAccountType::SAVINGS,
            bankCode: '033',
            bankName: 'Santander',
            agencyNumber: '4321',
            accountNumber: '98765',
            accountDigit: '1',
            initialBalance: Money::of('2000.00'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Conta bancária não encontrada.');

        $this->handler->handle($updateData);
    }

    /** @test */
    public function it_throws_exception_when_account_number_already_exists(): void
    {
        $bankAccountId = BankAccountId::generate();
        $otherBankAccountId = BankAccountId::generate();
        
        $existingBankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Antiga',
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

        $otherBankAccount = new BankAccount(
            id: $otherBankAccountId,
            name: 'Outra Conta',
            type: BankAccountType::SAVINGS,
            bankCode: '033',
            bankName: 'Santander',
            agencyNumber: '4321',
            accountNumber: '98765',
            accountDigit: '1',
            initialBalance: Money::of('2000.00'),
            currentBalance: Money::of('2500.00'),
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

        $updateData = new UpdateBankAccountData(
            id: $bankAccountId,
            name: 'Conta Nova',
            type: BankAccountType::SAVINGS,
            bankCode: '033',
            bankName: 'Santander',
            agencyNumber: '4321',
            accountNumber: '98765',
            accountDigit: '1',
            initialBalance: Money::of('2000.00'),
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($existingBankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findByAccountNumber')
            ->with('033', '4321', '98765')
            ->willReturn($otherBankAccount);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Já existe outra conta bancária com este número.');

        $this->handler->handle($updateData);
    }

    /** @test */
    public function it_unsets_other_default_accounts_when_marking_as_default(): void
    {
        $bankAccountId = BankAccountId::generate();
        $otherBankAccountId = BankAccountId::generate();
        
        $existingBankAccount = new BankAccount(
            id: $bankAccountId,
            name: 'Conta Antiga',
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

        $otherBankAccount = new BankAccount(
            id: $otherBankAccountId,
            name: 'Outra Conta',
            type: BankAccountType::SAVINGS,
            bankCode: '033',
            bankName: 'Santander',
            agencyNumber: '4321',
            accountNumber: '98765',
            accountDigit: '1',
            initialBalance: Money::of('2000.00'),
            currentBalance: Money::of('2500.00'),
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

        $updateData = new UpdateBankAccountData(
            id: $bankAccountId,
            name: 'Conta Nova',
            type: BankAccountType::SAVINGS,
            bankCode: '033',
            bankName: 'Santander',
            agencyNumber: '4321',
            accountNumber: '98765',
            accountDigit: '1',
            initialBalance: Money::of('2000.00'),
            isDefault: true,
        );

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findById')
            ->with($bankAccountId)
            ->willReturn($existingBankAccount);

        $this->bankAccountRepository
            ->expects($this->once())
            ->method('findByAccountNumber')
            ->with('033', '4321', '98765')
            ->willReturn(null);

        $this->bankAccountRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn(['data' => [$otherBankAccount]]);

        // Expect two saves: one for unsetting the other default account, one for updating the current account
        $this->bankAccountRepository
            ->expects($this->exactly(2))
            ->method('save');

        $result = $this->handler->handle($updateData);

        $this->assertInstanceOf(BankAccount::class, $result);
        $this->assertTrue($result->isDefault());
    }
}