<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\ValueObjects;

use InvalidArgumentException;

final readonly class TenantSchemaName
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Tenant schema name cannot be empty.');
        }

        if (! preg_match('/^tenant_[a-z0-9_]+$/', $value)) {
            throw new InvalidArgumentException('Tenant schema name has an invalid format.');
        }
    }

    public static function forSlug(TenantSlug $slug): self
    {
        return new self('tenant_'.$slug->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }
}
