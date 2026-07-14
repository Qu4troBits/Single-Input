<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\BankAccounts;

use App\Domain\BankAccounts\Entities\BankAccount;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BankAccountTest extends TestCase
{
    public function test_it_creates_bank_account_with_correct_properties(): void
    {
        $id = BankAccountId::generate(); 
        $initialBalance = Money::of('1000.50');
        $curentBalence = Money::of('1500.50');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $bankAccount = new BankAccount(
            id: $id,
            name: 'Conta Corrente',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: '001',
            bankName: 'Bank A',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            initialBalance: $initialBalance,
            currentBalance: $curentBalence,
            description: 'Conta principal',
            includeInDashboard: true,
            color: 'red',
            icon: 'bank',
            isDefault: true,
            includeInReports: true,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame($id, $bankAccount->getId());
        $this->assertSame('Conta Corrente', $bankAccount->getName());
        $this->assertSame(BankAccountType::CHECKING, $bankAccount->getType());
        $this->assertSame(BankAccountStatus::ACTIVE, $bankAccount->getStatus());
        $this->assertSame('001', $bankAccount->getBankCode());
        $this->assertSame('1234', $bankAccount->getAgencyNumber());
        $this->assertSame('567890', $bankAccount->getAccountNumber());
        $this->assertSame('1', $bankAccount->getAccountDigit());
        $this->assertSame('Conta principal', $bankAccount->getDescription());
        $this->assertEquals($curentBalence, $bankAccount->getCurrentBalance());
        $this->assertEquals($initialBalance, $bankAccount->getInitialBalance());
        $this->assertSame($createdAt, $bankAccount->getCreatedAt());
        $this->assertSame($updatedAt, $bankAccount->getUpdatedAt());
    }

    public function test_it_updates_bank_account_properties(): void
    {
        $bankAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Old Name',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: '001',
            bankName: 'Bank B',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            initialBalance: Money::of('10.00'),
            currentBalance: Money::of('700.00'),
            description: 'Old Description',
            color: 'blue',
            icon: 'bank',
            isDefault: true,
            includeInDashboard: false,
            includeInReports: true,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $oldUpdatedAt = $bankAccount->getUpdatedAt();
        $id = $bankAccount->getId();

        $bankAccount->update(
            name: 'New Name',
            type: BankAccountType::SAVINGS,
            bankName: 'Bank C',
            bankCode: '237',
            agencyNumber: '4321',
            accountNumber: '098765',
            accountDigit: '2',
            initialBalance: Money::of('10.00'),
            description: 'New Description',
            color: 'green',
            icon: 'bank',
            isDefault: false,
            includeInDashboard: true,
            includeInReports: true,
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertSame('New Name', $bankAccount->getName());
        $this->assertSame(BankAccountType::SAVINGS, $bankAccount->getType());
        $this->assertSame(BankAccountStatus::ACTIVE, $bankAccount->getStatus());
        $this->assertSame('237', $bankAccount->getBankCode());
        $this->assertSame('4321', $bankAccount->getAgencyNumber());
        $this->assertSame('098765', $bankAccount->getAccountNumber());
        $this->assertSame('2', $bankAccount->getAccountDigit());
        $this->assertSame('New Description', $bankAccount->getDescription());
        $this->assertGreaterThan($oldUpdatedAt, $bankAccount->getUpdatedAt());
    }

    public function test_it_updates_balance(): void
    {
        $bankAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Test Account',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: '001',
            bankName: 'Bank A',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1001.00'),
            description: null,
            color: 'red',
            icon: 'bank',
            isDefault: true,
            includeInDashboard: true,
            includeInReports: true,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $oldUpdatedAt = $bankAccount->getUpdatedAt();

        $newBalance = Money::of('1500.75');
        $bankAccount->updateBalance($newBalance, new \DateTimeImmutable());

        $this->assertEquals($newBalance, $bankAccount->getCurrentBalance());
        $this->assertGreaterThan($oldUpdatedAt, $bankAccount->getUpdatedAt());
    }

    public function test_it_checks_if_account_is_active(): void
    {
        $activeAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Active Account',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: '001',
            bankName: 'Bank D',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            color: 'red',
            icon: 'bank',
            isDefault: true,
            includeInDashboard: true,
            includeInReports: true,
            description: null,
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('1670.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $inactiveAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Inactive Account',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::INACTIVE,
            bankCode: '001',
            bankName: 'Bank B',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            color: 'blue',
            icon: 'bank',
            isDefault: true,
            includeInDashboard: false,
            includeInReports: true,
            description: null,
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('10.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($activeAccount->isActive());
        $this->assertFalse($inactiveAccount->isActive());
    }

    public function test_it_checks_account_type(): void
    {
        $checkingAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Checking Account',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: '001',
            bankName: 'Bank A',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            color: 'red',
            icon: 'bank',
            isDefault: true,
            includeInDashboard: true,
            includeInReports: true,
            description: null,
            initialBalance: Money::of('1000.00'),
            currentBalance: Money::of('20000.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $creditCardAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Credit Card Account',
            type: BankAccountType::OTHER,
            status: BankAccountStatus::ACTIVE,
            bankCode: '003',
            bankName: 'Bank C',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            description: null,
            color: 'red',
            icon: 'bank',
            isDefault: true,
            includeInDashboard: true,
            includeInReports: true,
            initialBalance: Money::of('10.00'),
            currentBalance: Money::of('2561.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($checkingAccount->isChecking());
        $this->assertFalse($creditCardAccount->isChecking());
    }
}