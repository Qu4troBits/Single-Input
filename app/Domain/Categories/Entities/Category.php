<?php

declare(strict_types=1);

namespace App\Domain\Categories\Entities;

use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryType;
use DateTimeImmutable;

final class Category
{
    private ?DateTimeImmutable $deletedAt = null;
    private ?DateTimeImmutable $archivedAt = null;

    public function __construct(
        private CategoryId $id,
        private string $name,
        private CategoryType $type,
        private string $code,
        private ?string $description = null,
        private ?string $color = null,
        private ?string $icon = null,
        private bool $isOperating = true,
        private bool $isTaxDeductible = false,
        private bool $includeInReports = true,
        private bool $isDefault = false,
        private ?CategoryId $parentId = null,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $archivedAt = null,
    ) {
        $this->archivedAt = $archivedAt;
    }

    public function getId(): CategoryId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): CategoryType
    {
        return $this->type;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function isOperating(): bool
    {
        return $this->isOperating;
    }

    public function isTaxDeductible(): bool
    {
        return $this->isTaxDeductible;
    }

    public function isIncludeInReports(): bool
    {
        return $this->includeInReports;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getArchivedAt(): ?DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }

    public function archive(DateTimeImmutable $archivedAt): void
    {
        $this->archivedAt = $archivedAt;
        $this->updatedAt = $archivedAt;
    }

    public function restore(DateTimeImmutable $updatedAt): void
    {
        $this->archivedAt = null;
        $this->updatedAt = $updatedAt;
    }

    public function update(
        string $name,
        CategoryType $type,
        string $code,
        ?string $description = null,
        ?string $color = null,
        ?string $icon = null,
        bool $isOperating = true,
        bool $isTaxDeductible = false,
        bool $includeInReports = true,
        bool $isDefault = false,
        ?CategoryId $parentId = null,
        DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $archivedAt = null,
    ): void {
        $this->name = $name;
        $this->type = $type;
        $this->code = $code;
        $this->description = $description;
        $this->color = $color;
        $this->icon = $icon;
        $this->isOperating = $isOperating;
        $this->isTaxDeductible = $isTaxDeductible;
        $this->includeInReports = $includeInReports;
        $this->isDefault = $isDefault;
        $this->parentId = $parentId;
        $this->updatedAt = $updatedAt;
        $this->archivedAt = $archivedAt;
    }

    public function markAsDeleted(DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
        $this->updatedAt = $deletedAt;
    }

    public function isRevenue(): bool
    {
        return $this->type->isRevenue();
    }

    public function isExpense(): bool
    {
        return $this->type->isExpense();
    }

    public function isTransfer(): bool
    {
        return $this->type->isTransfer();
    }

    public function hasParent(): bool
    {
        return $this->parentId !== null;
    }

    public function isRoot(): bool
    {
        return $this->parentId === null;
    }
}
