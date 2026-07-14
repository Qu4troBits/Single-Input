<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract readonly class UuidIdentifier
{
    private function __construct(
        private UuidInterface $value,
    ) {}

    public static function generate(): static
    {
        return new static(Uuid::uuid7());
    }

    public static function fromString(string $value): static
    {
        return new static(Uuid::fromString($value));
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }
}