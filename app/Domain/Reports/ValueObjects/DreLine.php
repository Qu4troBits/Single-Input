<?php

declare(strict_types=1);

namespace App\Domain\Reports\ValueObjects;

use App\Domain\Reports\DreLineType;
use App\Domain\Shared\Money;

final readonly class DreLine
{
    public function __construct(
        private string $id,
        private string $code,
        private string $description,
        private Money $amount,
        private DreLineType $type,
        private int $level = 1,
        private bool $isOperating = true,
        private ?string $parentId = null,
        private ?string $categoryId = null,
        private ?string $categoryName = null,
        private ?string $notes = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('ID cannot be empty.');
        }

        if (empty($this->code)) {
            throw new \InvalidArgumentException('Code cannot be empty.');
        }

        if (strlen($this->code) > 20) {
            throw new \InvalidArgumentException('Code cannot exceed 20 characters.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }

        if (strlen($this->description) > 255) {
            throw new \InvalidArgumentException('Description cannot exceed 255 characters.');
        }

        if ($this->amount->isNegative()) {
            throw new \InvalidArgumentException('Amount cannot be negative.');
        }

        if ($this->level < 1 || $this->level > 5) {
            throw new \InvalidArgumentException('Level must be between 1 and 5.');
        }

        if ($this->categoryName !== null && strlen($this->categoryName) > 100) {
            throw new \InvalidArgumentException('Category name cannot exceed 100 characters.');
        }

        if ($this->notes !== null && strlen($this->notes) > 1000) {
            throw new \InvalidArgumentException('Notes cannot exceed 1000 characters.');
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getType(): DreLineType
    {
        return $this->type;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function isOperating(): bool
    {
        return $this->isOperating;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function isRevenue(): bool
    {
        return $this->type === DreLineType::REVENUE;
    }

    public function isExpense(): bool
    {
        return $this->type === DreLineType::EXPENSE;
    }

    public function isProfit(): bool
    {
        return $this->type === DreLineType::PROFIT;
    }

    public function isTopLevel(): bool
    {
        return $this->level === 1;
    }

    public function isDetail(): bool
    {
        return $this->level > 1;
    }

    public function getIndentation(): string
    {
        return str_repeat('  ', $this->level - 1);
    }

    public function getFormattedAmount(): string
    {
        $sign = $this->type->getSign();
        $formatted = $this->amount->format();
        
        if ($sign === -1) {
            return '-' . $formatted;
        }
        
        return $formatted;
    }

    public function getSignedAmount(): Money
    {
        $sign = $this->type->getSign();
        
        if ($sign === -1) {
            return $this->amount->multiply('-1');
        }
        
        return $this->amount;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'amount' => $this->amount->toNumeric(),
            'formatted_amount' => $this->getFormattedAmount(),
            'type' => $this->type->value,
            'type_label' => $this->type->getLabel(),
            'level' => $this->level,
            'is_operating' => $this->isOperating,
            'parent_id' => $this->parentId,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'notes' => $this->notes,
            'indentation' => $this->getIndentation(),
        ];
    }

    public static function createRevenueLine(
        string $id,
        string $code,
        string $description,
        Money $amount,
        int $level = 1,
        bool $isOperating = true,
        ?string $parentId = null,
        ?string $categoryId = null,
        ?string $categoryName = null,
        ?string $notes = null,
    ): self {
        return new self(
            $id,
            $code,
            $description,
            $amount,
            DreLineType::REVENUE,
            $level,
            $isOperating,
            $parentId,
            $categoryId,
            $categoryName,
            $notes
        );
    }

    public static function createExpenseLine(
        string $id,
        string $code,
        string $description,
        Money $amount,
        int $level = 1,
        bool $isOperating = true,
        ?string $parentId = null,
        ?string $categoryId = null,
        ?string $categoryName = null,
        ?string $notes = null,
    ): self {
        return new self(
            $id,
            $code,
            $description,
            $amount,
            DreLineType::EXPENSE,
            $level,
            $isOperating,
            $parentId,
            $categoryId,
            $categoryName,
            $notes
        );
    }

    public static function createProfitLine(
        string $id,
        string $code,
        string $description,
        Money $amount,
        int $level = 1,
        bool $isOperating = true,
        ?string $parentId = null,
        ?string $categoryId = null,
        ?string $categoryName = null,
        ?string $notes = null,
    ): self {
        return new self(
            $id,
            $code,
            $description,
            $amount,
            DreLineType::PROFIT,
            $level,
            $isOperating,
            $parentId,
            $categoryId,
            $categoryName,
            $notes
        );
    }
}