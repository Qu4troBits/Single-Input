<?php

declare(strict_types=1);

namespace App\Domain\Categories\ValueObjects;

use App\Domain\Shared\UuidIdentifier;

final class CategoryId extends UuidIdentifier
{
    public static function getPrefix(): string
    {
        return 'cat_';
    }
}
