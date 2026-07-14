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
            'total' => $this->getTotal()->toNumeric(),
        ];
    }

    public function removeItem(string $itemId): void
    {
        $this->items = array_values(
            array_filter($this->items, fn(ProjectionItem $item) => $item->getId() !== $itemId)
        );
    }

    public function updateItem(string|ProjectionItem $itemIdOrItem, array $data = []): void
    {
        if ($itemIdOrItem instanceof ProjectionItem) {
            $itemId = $itemIdOrItem->getId();
            foreach ($this->items as $index => $item) {
                if ($item->getId() === $itemId) {
                    $this->items[$index] = $itemIdOrItem;
                    break;
                }
            }
        } else {
            foreach ($this->items as $index => $item) {
                if ($item->getId() === $itemIdOrItem) {
                    $this->items[$index] = $item->update($data);
                    break;
                }
            }
        }
    }

    public function getItem(string $itemId): ?ProjectionItem
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $itemId) {
                return $item;
            }
        }
        return null;
    }

    public function getItemsByCategory(?string $categoryId = null): array
    {
        if ($categoryId === null) {
            return array_values($this->items);
        }
        return array_values(
            array_filter($this->items, fn(ProjectionItem $item) => $item->getCategoryId() === $categoryId)
        );
    }

    public function getItemsByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return array_values(
            array_filter(
                $this->items,
                fn(ProjectionItem $item) => $item->getDate() >= $startDate && $item->getDate() <= $endDate
            )
        );
    }
}