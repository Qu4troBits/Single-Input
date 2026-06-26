<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections;

use App\Domain\Shared\Money;

final class FinancialProjection
{
    /** @var array<ProjectionItem> */
    private array $items = [];

    public function __construct(
        private readonly ProjectionPeriod $period,
        private readonly ProjectionType $type,
        private readonly string $title,
        private readonly ?string $categoryId = null,
        private readonly ?string $scenario = 'base',
    ) {}

    public function addItem(ProjectionItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return array<ProjectionItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): Money
    {
        $total = Money::zero();
        
        foreach ($this->items as $item) {
            $total = $total->add($item->getAmount());
        }
        
        return $total;
    }

    public function getPeriod(): ProjectionPeriod
    {
        return $this->period;
    }

    public function getType(): ProjectionType
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getScenario(): string
    {
        return $this->scenario;
    }

    public function toArray(): array
    {
        return [
            'period' => [
                'start_date' => $this->period->getStartDate()->format('Y-m-d'),
                'end_date' => $this->period->getEndDate()->format('Y-m-d'),
                'type' => $this->period->getPeriodType()->value,
                'label' => $this->period->getLabel(),
            ],
            'type' => $this->type->value,
            'title' => $this->title,
            'category_id' => $this->categoryId,
            'scenario' => $this->scenario,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'total' => $this->getTotal()->getAmount(),
        ];
    }
}