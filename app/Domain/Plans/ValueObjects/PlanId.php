<?php

declare(strict_types=1);

namespace App\Domain\Plans\ValueObjects;

use App\Domain\Shared\UuidIdentifier;

final readonly class PlanId extends UuidIdentifier
{
    public static function getPrefix(): string
    {
        return 'plan_';
    }
}
