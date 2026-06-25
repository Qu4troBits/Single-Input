<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\ValueObjects;

use InvalidArgumentException;

final readonly class TenantSlug
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Tenant slug cannot be empty.');
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            throw new InvalidArgumentException('Tenant slug has an invalid format.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
