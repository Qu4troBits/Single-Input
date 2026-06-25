<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Finance\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_it_normalizes_scale_and_supports_operations(): void
    {
        $a = Money::of('10');
        $b = Money::of('2.50');

        self::assertSame('10.00', $a->toString());
        self::assertSame('12.50', $a->add($b)->toString());
        self::assertSame('7.50', $a->subtract($b)->toString());
        self::assertSame('25.00', $b->multiply(10)->toString());
    }

    public function test_it_rejects_invalid_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::of('10.999');
    }
}

