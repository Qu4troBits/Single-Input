<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Transactions;

use App\Domain\Transactions\TransactionStatus;
use PHPUnit\Framework\TestCase;

final class TransactionStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_values(): void
    {
        $this->assertSame('pending', TransactionStatus::PENDING->value);
        $this->assertSame('paid', TransactionStatus::PAID->value);
        $this->assertSame('cancelled', TransactionStatus::CANCELLED->value);
        $this->assertSame('reversed', TransactionStatus::REVERSED->value);
    }

    /** @test */
    public function it_can_be_created_from_string(): void
    {
        $this->assertSame(TransactionStatus::PENDING, TransactionStatus::from('pending'));
        $this->assertSame(TransactionStatus::PAID, TransactionStatus::from('paid'));
        $this->assertSame(TransactionStatus::CANCELLED, TransactionStatus::from('cancelled'));
        $this->assertSame(TransactionStatus::REVERSED, TransactionStatus::from('reversed'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        TransactionStatus::from('invalid');
    }

    /** @test */
    public function it_checks_if_status_can_be_paid(): void
    {
        $this->assertTrue(TransactionStatus::PENDING->canBePaid());
        $this->assertFalse(TransactionStatus::PAID->canBePaid());
        $this->assertFalse(TransactionStatus::CANCELLED->canBePaid());
        $this->assertFalse(TransactionStatus::REVERSED->canBePaid());
    }

    /** @test */
    public function it_checks_if_status_can_be_cancelled(): void
    {
        $this->assertTrue(TransactionStatus::PENDING->canBeCancelled());
        $this->assertTrue(TransactionStatus::PAID->canBeCancelled());
        $this->assertFalse(TransactionStatus::CANCELLED->canBeCancelled());
        $this->assertFalse(TransactionStatus::REVERSED->canBeCancelled());
    }

    /** @test */
    public function it_returns_all_cases(): void
    {
        $cases = TransactionStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(TransactionStatus::PENDING, $cases);
        $this->assertContains(TransactionStatus::PAID, $cases);
        $this->assertContains(TransactionStatus::CANCELLED, $cases);
        $this->assertContains(TransactionStatus::REVERSED, $cases);
    }
}