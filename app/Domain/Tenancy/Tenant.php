<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Plans\ValueObjects\PlanId;
use App\Domain\Tenancy\ValueObjects\TenantId;
use App\Domain\Tenancy\ValueObjects\TenantSchemaName;
use App\Domain\Tenancy\ValueObjects\TenantSlug;

final class Tenant
{
    public function __construct(
        public TenantId $id,
        public TenantSlug $slug,
        public string $name,
        public TenantSchemaName $schemaName,
        public PlanId $planId,
        public ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public static function create(
        TenantId $id,
        string $slug,
        string $name,
        PlanId $planId,
        string $dbSchema,
    ): self {
        return new self(
            id: $id,
            slug: TenantSlug::fromString($slug),
            name: $name,
            schemaName: TenantSchemaName::fromString($dbSchema),
            planId: $planId,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'slug' => $this->slug->toString(),
            'name' => $this->name,
            'schema_name' => $this->schemaName->toString(),
            'plan_id' => $this->planId->toString(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
