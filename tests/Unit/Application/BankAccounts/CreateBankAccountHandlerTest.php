<?php

declare(strict_types=1);

namespace Tests\Unit\Application\BankAccounts;

use App\Application\BankAccounts\DTOs\CreateBankAccountData;
use App\Application\BankAccounts\Handlers\CreateBankAccountHandler;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
 
final class CreateBankAccountHandlerTest extends TestCase
{
    private BankAccountRepositoryInterface&MockObject $repository;
    private CreateBankAccountHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(BankAccountRepositoryInterface::class);
        $this->handler = new CreateBankAccountHandler($this->repository);
    }

    public function test_it_creates_bank_account(): void
    {
        $data = new CreateBankAccountData(
            name: 'Conta Corrente',
            type: BankAccountType::CHECKING,
            bankCode: '001',
            bankName: 'Banco do Brasil',
            agencyNumber: '1234',
            accountNumber: '567890',
            accountDigit: '1',
            description: 'Conta principal',
            initialBalance: Money::of('1000.50'),
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($bankAccount) use ($data) {
                return $bankAccount->getName() === $data->name
                    && $bankAccount->getType() === $data->type
                    && $bankAccount->getBankCode() === $data->bankCode
                    && $bankAccount->getAgencyNumber() === $data->agencyNumber
                    && $bankAccount->getAccountNumber() === $data->accountNumber
                    && $bankAccount->getAccountDigit() === $data->accountDigit
                    && $bankAccount->getDescription() === $data->description
                    && $bankAccount->getInitialBalance()->equals($data->initialBalance);
            }));

        $bankAccountId = $this->handler->handle($data);

        $this->assertNotNull($bankAccountId);
    }

    public function test_it_creates_bank_account_with_nullable_fields(): void
    {
        $data = new CreateBankAccountData(
            name: 'Carteira Digital',
            type: BankAccountType::WALLET,
            bankCode: '012',
            bankName: 'Nubank',
            agencyNumber: '1234',
            accountNumber: '',
            accountDigit: null,
            description: null,
            initialBalance: Money::of('500.00'),
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($bankAccount) use ($data) {
                return $bankAccount->getName() === $data->name
                    && $bankAccount->getType() === $data->type
                    && $bankAccount->getBankCode() === $data->bankCode
                    && $bankAccount->getAgencyNumber() === $data->agencyNumber
                    && $bankAccount->getAccountNumber() === $data->accountNumber
                    && $bankAccount->getAccountDigit() === $data->accountDigit
                    && $bankAccount->getDescription() === $data->description
                    && $bankAccount->getInitialBalance()->equals($data->initialBalance);
            }));

        $bankAccountId = $this->handler->handle($data);

        $this->assertNotNull($bankAccountId);
    }

    public function test_it_creates_bank_account_with_different_types(): void
    {
        $types = [
            BankAccountType::CHECKING,
            BankAccountType::SAVINGS,
            BankAccountType::INVESTMENT,
            BankAccountType::WALLET,
            BankAccountType::OTHER,
        ];

        $this->repository
            ->expects($this->exactly(count($types)))
            ->method('save')
            ->with($this->callback(function ($bankAccount) use ($types) {
                return in_array($bankAccount->getType(), $types, true);
            }));

        foreach ($types as $type) {
            $data = new CreateBankAccountData(
                name: "Account {$type->value}",
                type: $type,
                bankCode: '002',
                bankName: 'Bradesco',
                agencyNumber: '1234',
                accountNumber: '',
                accountDigit: null,
                description: null,
                initialBalance: Money::of('100.00'),
            );

            $bankAccountId = $this->handler->handle($data);

            $this->assertNotNull($bankAccountId);
        }
    }
}