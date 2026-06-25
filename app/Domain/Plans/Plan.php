<?php

declare(strict_types=1);

namespace App\Domain\Plans;

final readonly class Plan
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
    ) {
    }
}
