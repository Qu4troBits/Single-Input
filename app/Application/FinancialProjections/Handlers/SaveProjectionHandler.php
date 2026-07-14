<?php

declare(strict_types=1);

namespace App\Application\FinancialProjections\Handlers;

use App\Application\FinancialProjections\DTOs\SaveProjectionData;
use App\Domain\FinancialProjections\FinancialProjection;
use App\Domain\FinancialProjections\FinancialProjectionRepositoryInterface;
use App\Domain\FinancialProjections\ProjectionItem;
use App\Domain\FinancialProjections\ProjectionPeriod;
use App\Domain\FinancialProjections\ProjectionType;
use App\Domain\Shared\Money;

final readonly class SaveProjectionHandler
{
    public function __construct(
        private FinancialProjectionRepositoryInterface $projectionRepository,
    ) {}

    public function handle(SaveProjectionData $data): void
    {
        $period = $this->createProjectionPeriod($data);
        
        $projection = new FinancialProjection(
            period: $period,
            type: is_string($data->type) ? ProjectionType::from($data->type) : $data->type,
            title: $data->title,
            categoryId: $data->categoryId,
            scenario: $data->scenario,
        );

        foreach ($data->items as $index => $itemData) {
            $projection->addItem(new ProjectionItem(
                id: $data->id . '-item-' . $index,
                date: \DateTimeImmutable::createFromFormat('Y-m-d', $itemData->date),
                description: $itemData->description,
                amount: Money::of($itemData->amount),
                type: is_string($itemData->type) ? ProjectionType::from($itemData->type) : $itemData->type,
                categoryId: $itemData->categoryId,
                categoryName: $itemData->categoryName,
                notes: $itemData->notes,
                source: $itemData->source,
            ));
        }

        $this->projectionRepository->save($projection);
    }

    private function createProjectionPeriod(SaveProjectionData $data): ProjectionPeriod
    {
        return match ($data->periodType) {
            'monthly' => ProjectionPeriod::createMonthly($data->yearMonth),
            'quarterly' => ProjectionPeriod::createQuarterly($data->year, $data->quarter),
            'yearly' => ProjectionPeriod::createYearly($data->year),
            default => throw new \InvalidArgumentException('Invalid period type.'),
        };
    }
}