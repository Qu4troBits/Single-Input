<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use InvalidArgumentException;

final readonly class Money
{
    private string $amount;

    private function __construct(string $amount)
    {
        if (!preg_match('/^-?\d+(\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Invalid money format.');
        }

        $this->amount = $amount;
    }

    public static function of(string $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self('0.00');
    }

    public function add(self $other): self
    {
        $result = bcadd($this->amount, $other->amount, 2);
        return new self($result);
    }

    public function subtract(self $other): self
    {
        $result = bcsub($this->amount, $other->amount, 2);
        return new self($result);
    }

    public function multiply(string $multiplier): self
    {
        $result = bcmul($this->amount, $multiplier, 2);
        return new self($result);
    }

    public function divide(string $divisor): self
    {
        if (bccomp($divisor, '0', 2) === 0) {
            throw new InvalidArgumentException('Division by zero.');
        }

        $result = bcdiv($this->amount, $divisor, 2);
        return new self($result);
    }

    public function compare(self $other): int
    {
        return bccomp($this->amount, $other->amount, 2);
    }

    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function isLessThan(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isPositive(): bool
    {
        return $this->compare(self::zero()) > 0;
    }

    public function isNegative(): bool
    {
        return $this->compare(self::zero()) < 0;
    }

    public function isZero(): bool
    {
        return $this->compare(self::zero()) === 0;
    }

    public function toNumeric(): string
    {
        return $this->amount;
    }

    public function __toString(): string
    {
        return $this->amount;
    }
}