<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Transactions;

use App\Domain\Shared\Money;
use App\Domain\Transactions\Transaction;
use App\Domain\Transactions\TransactionId;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Transactions\TransactionDirection;
use App\Domain\Transactions\TransactionStatus;
use PHPUnit\Framework\TestCase;

final class TransactionTest extends TestCase
{
    private TransactionId $transactionId;
    private BankAccountId $bankAccountId;
    private CategoryId $categoryId;
    private Money $amount;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionId = TransactionId::generate();
        $this->bankAccountId = BankAccountId::generate();
        $this->categoryId = CategoryId::generate();
        $this->amount = Money::of('1500.50');
        $this->createdAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $this->updatedAt = new \DateTimeImmutable('2024-01-15 10:00:00');
    }

    /** @test */
    public function it_can_be_created_with_valid_data(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PENDING,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $this->assertSame($this->transactionId, $transaction->getId());
        $this->assertSame($this->bankAccountId, $transaction->getBankAccountId());
        $this->assertSame($this->categoryId, $transaction->getCategoryId());
        $this->assertSame('Pagamento de fornecedor', $transaction->getDescription());
        $this->assertSame($this->amount, $transaction->getAmount());
        $this->assertSame(TransactionDirection::OUT, $transaction->getDirection());
        $this->assertSame(TransactionStatus::PENDING, $transaction->getStatus());
        $this->assertSame('2024-01', $transaction->getCompetenceMonth());
        $this->assertNull($transaction->getPaymentDate());
        $this->assertSame($this->createdAt, $transaction->getCreatedAt());
        $this->assertSame($this->updatedAt, $transaction->getUpdatedAt());
    }

    /** @test */
    public function it_can_be_marked_as_paid(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PENDING,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $paymentDate = new \DateTimeImmutable('2024-01-20');
        $transaction->markAsPaid($paymentDate);

        $this->assertSame(TransactionStatus::PAID, $transaction->getStatus());
        $this->assertSame($paymentDate, $transaction->getPaymentDate());
        $this->assertGreaterThan($this->updatedAt, $transaction->getUpdatedAt());
    }

    /** @test */
    public function it_cannot_be_marked_as_paid_if_already_paid(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PAID,
            competenceMonth: '2024-01',
            paymentDate: new \DateTimeImmutable('2024-01-20'),
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction cannot be marked as paid.');

        $transaction->markAsPaid(new \DateTimeImmutable('2024-01-21'));
    }

    /** @test */
    public function it_cannot_be_marked_as_paid_if_cancelled(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::CANCELLED,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction cannot be marked as paid.');

        $transaction->markAsPaid(new \DateTimeImmutable('2024-01-21'));
    }

    /** @test */
    public function it_can_be_marked_as_cancelled(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PENDING,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $transaction->markAsCancelled();

        $this->assertSame(TransactionStatus::CANCELLED, $transaction->getStatus());
        $this->assertGreaterThan($this->updatedAt, $transaction->getUpdatedAt());
    }

    /** @test */
    public function it_can_be_marked_as_cancelled_if_already_paid(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PAID,
            competenceMonth: '2024-01',
            paymentDate: new \DateTimeImmutable('2024-01-20'),
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $transaction->markAsCancelled();

        $this->assertSame(TransactionStatus::CANCELLED, $transaction->getStatus());
        $this->assertGreaterThan($this->updatedAt, $transaction->getUpdatedAt());
    }

    /** @test */
    public function it_cannot_be_marked_as_cancelled_if_already_cancelled(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::CANCELLED,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction cannot be cancelled.');

        $transaction->markAsCancelled();
    }

    /** @test */
    public function it_can_be_updated(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PENDING,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $newBankAccountId = BankAccountId::generate();
        $newCategoryId = CategoryId::generate();
        $newAmount = Money::of('2000.00');
        $newDescription = 'Pagamento atualizado';
        $newCompetenceMonth = '2024-02';

        $transaction->update(
            bankAccountId: $newBankAccountId,
            categoryId: $newCategoryId,
            description: $newDescription,
            amount: $newAmount,
            direction: TransactionDirection::IN,
            competenceMonth: $newCompetenceMonth,
        );

        $this->assertSame($newBankAccountId, $transaction->getBankAccountId());
        $this->assertSame($newCategoryId, $transaction->getCategoryId());
        $this->assertSame($newDescription, $transaction->getDescription());
        $this->assertSame($newAmount, $transaction->getAmount());
        $this->assertSame(TransactionDirection::IN, $transaction->getDirection());
        $this->assertSame($newCompetenceMonth, $transaction->getCompetenceMonth());
        $this->assertGreaterThan($this->updatedAt, $transaction->getUpdatedAt());
    }

    /** @test */
    public function it_calculates_signed_amount_for_income(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Recebimento de cliente',
            amount: $this->amount,
            direction: TransactionDirection::IN,
            status: TransactionStatus::PENDING,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $signedAmount = $transaction->getSignedAmount();

        $this->assertSame($this->amount, $signedAmount);
        $this->assertSame('1500.50', $signedAmount->toNumeric());
    }

    /** @test */
    public function it_calculates_signed_amount_for_expense(): void
    {
        $transaction = new Transaction(
            id: $this->transactionId,
            bankAccountId: $this->bankAccountId,
            categoryId: $this->categoryId,
            description: 'Pagamento de fornecedor',
            amount: $this->amount,
            direction: TransactionDirection::OUT,
            status: TransactionStatus::PENDING,
            competenceMonth: '2024-01',
            paymentDate: null,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );

        $signedAmount = $transaction->getSignedAmount();

        $this->assertSame('-1500.50', $signedAmount->toNumeric());
    }


}