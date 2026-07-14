<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Transactions;

use App\Domain\Transactions\TransactionDirection;
use PHPUnit\Framework\TestCase;

final class TransactionDirectionTest extends TestCase
{
    /** @test */
    public function it_has_correct_values(): void
    {
        $this->assertSame('in', TransactionDirection::IN->value);
        $this->assertSame('out', TransactionDirection::OUT->value);
    }

    /** @test */
    public function it_can_be_created_from_string(): void
    {
        $this->assertSame(TransactionDirection::IN, TransactionDirection::from('in'));
        $this->assertSame(TransactionDirection::OUT, TransactionDirection::from('out'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        TransactionDirection::from('invalid');
    }

    /** @test */
    public function it_checks_if_direction_is_income(): void
    {
        $this->assertTrue(TransactionDirection::IN->isIncome());
        $this->assertFalse(TransactionDirection::OUT->isIncome());
    }

    /** @test */
    public function it_checks_if_direction_is_expense(): void
    {
        $this->assertFalse(TransactionDirection::IN->isExpense());
        $this->assertTrue(TransactionDirection::OUT->isExpense());
    }

    /** @test */
    public function it_returns_correct_sign(): void
    {
        $this->assertSame(1, TransactionDirection::IN->getSign());
        $this->assertSame(-1, TransactionDirection::OUT->getSign());
    }

    /** @test */
    public function it_returns_all_cases(): void
    {
        $cases = TransactionDirection::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(TransactionDirection::IN, $cases);
        $this->assertContains(TransactionDirection::OUT, $cases);
    }
}