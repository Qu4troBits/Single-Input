<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\BankAccounts;

use App\Domain\BankAccounts\BankAccount;
use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountStatus;
use App\Domain\BankAccounts\BankAccountType;
use App\Domain\Shared\Money;
use PHPUnit\Framework\TestCase;

final class BankAccountTest extends TestCase
{
    public function test_it_creates_bank_account_with_correct_properties(): void
    {
        $id = BankAccountId::generate();
        $initialBalance = Money::of('1000.50');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $bankAccount = new BankAccount(
            id: $id,
            name: 'Conta Corrente',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: '001',
            agency: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            description: 'Conta principal',
            initialBalance: $initialBalance,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame($id, $bankAccount->getId());
        $this->assertSame('Conta Corrente', $bankAccount->getName());
        $this->assertSame(BankAccountType::CHECKING, $bankAccount->getType());
        $this->assertSame(BankAccountStatus::ACTIVE, $bankAccount->getStatus());
        $this->assertSame('001', $bankAccount->getBankCode());
        $this->assertSame('1234', $bankAccount->getAgency());
        $this->assertSame('567890', $bankAccount->getAccountNumber());
        $this->assertSame('1', $bankAccount->getAccountDigit());
        $this->assertSame('Conta principal', $bankAccount->getDescription());
        $this->assertEquals($initialBalance, $bankAccount->getBalance());
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
            agency: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            description: 'Old Description',
            initialBalance: Money::of('1000.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $oldUpdatedAt = $bankAccount->getUpdatedAt();

        $bankAccount->update(
            name: 'New Name',
            type: BankAccountType::SAVINGS,
            status: BankAccountStatus::INACTIVE,
            bankCode: '237',
            agency: '4321',
            accountNumber: '098765',
            accountDigit: '2',
            description: 'New Description',
        );

        $this->assertSame('New Name', $bankAccount->getName());
        $this->assertSame(BankAccountType::SAVINGS, $bankAccount->getType());
        $this->assertSame(BankAccountStatus::INACTIVE, $bankAccount->getStatus());
        $this->assertSame('237', $bankAccount->getBankCode());
        $this->assertSame('4321', $bankAccount->getAgency());
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
            bankCode: null,
            agency: null,
            accountNumber: null,
            accountDigit: null,
            description: null,
            initialBalance: Money::of('1000.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $oldUpdatedAt = $bankAccount->getUpdatedAt();

        $newBalance = Money::of('1500.75');
        $bankAccount->updateBalance($newBalance);

        $this->assertEquals($newBalance, $bankAccount->getBalance());
        $this->assertGreaterThan($oldUpdatedAt, $bankAccount->getUpdatedAt());
    }

    public function test_it_checks_if_account_is_active(): void
    {
        $activeAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Active Account',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::ACTIVE,
            bankCode: null,
            agency: null,
            accountNumber: null,
            accountDigit: null,
            description: null,
            initialBalance: Money::of('1000.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $inactiveAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Inactive Account',
            type: BankAccountType::CHECKING,
            status: BankAccountStatus::INACTIVE,
            bankCode: null,
            agency: null,
            accountNumber: null,
            accountDigit: null,
            description: null,
            initialBalance: Money::of('1000.00'),
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
            bankCode: null,
            agency: null,
            accountNumber: null,
            accountDigit: null,
            description: null,
            initialBalance: Money::of('1000.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $creditCardAccount = new BankAccount(
            id: BankAccountId::generate(),
            name: 'Credit Card Account',
            type: BankAccountType::CREDIT_CARD,
            status: BankAccountStatus::ACTIVE,
            bankCode: null,
            agency: null,
            accountNumber: null,
            accountDigit: null,
            description: null,
            initialBalance: Money::of('1000.00'),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertTrue($checkingAccount->isChecking());
        $this->assertFalse($creditCardAccount->isChecking());
        $this->assertTrue($creditCardAccount->isCreditCard());
        $this->assertFalse($checkingAccount->isCreditCard());
    }
}