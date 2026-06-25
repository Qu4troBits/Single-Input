<?php

declare(strict_types=1);

namespace App\Domain\Categories;

enum CategoryStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ARCHIVED = 'archived';
}