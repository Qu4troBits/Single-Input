<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(private string $amount)
    {
    }

    public static function of(string $amount): self
    {
        $amount = trim($amount);

        if ($amount === '') {
            throw new InvalidArgumentException('Money amount cannot be empty.');
        }

        if (! preg_match('/^-?\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Money amount must have up to 2 decimal places.');
        }

        return new self(bcadd($amount, '0', 2));
    }

    public function add(self $other): self
    {
        return new self(bcadd($this->amount, $other->amount, 2));
    }

    public function subtract(self $other): self
    {
        return new self(bcsub($this->amount, $other->amount, 2));
    }

    public function multiply(int|string $multiplier): self
    {
        $multiplier = (string) $multiplier;

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $multiplier)) {
            throw new InvalidArgumentException('Money multiplier must be numeric.');
        }

        return new self(bcmul($this->amount, $multiplier, 2));
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', 2) === -1;
    }

    public function toString(): string
    {
        return $this->amount;
    }
}
