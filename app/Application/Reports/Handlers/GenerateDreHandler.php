<?php

declare(strict_types=1);

namespace App\Application\Reports\Handlers;

use App\Application\Reports\DTOs\GenerateDreData;
use App\Domain\Reports\Dre;
use App\Domain\Reports\Services\DreGenerator;
use App\Domain\Reports\ValueObjects\DrePeriod;

final readonly class GenerateDreHandler
{
    public function __construct(
        private DreGenerator $dreGenerator,
    ) {}

    public function handle(GenerateDreData $data): Dre
    {
        $period = $this->createDrePeriod($data);
        
        $title = $this->dreGenerator->generateDreTitle(
            $period,
            null, // category name will be fetched by repository if needed
            $data->scenario
        );

        $dre = $this->dreGenerator->generate($period, $data->categoryId, $data->scenario);
        
        // Atualiza o título com base nos dados gerados
        $dreArray = $dre->toArray();
        $dreArray['title'] = $title;
        
        // Cria um novo DRE com o título atualizado
        return new Dre(
            $dre->getPeriod(),
            $title,
            $dre->getCategoryId(),
            $dre->getScenario()
        );
    }

    private function createDrePeriod(GenerateDreData $data): DrePeriod
    {
        return match ($data->periodType) {
            'monthly' => DrePeriod::createMonthly($data->yearMonth),
            'quarterly' => DrePeriod::createQuarterly($data->year, $data->quarter),
            'yearly' => DrePeriod::createYearly($data->year),
            'custom' => DrePeriod::createCustom(
                new \DateTimeImmutable($data->startDate),
                new \DateTimeImmutable($data->endDate)
            ),
            default => throw new \InvalidArgumentException('Invalid period type.'),
        };
    }

    public function handleConsolidated(array $periodsData, ?string $categoryId = null, string $scenario = 'base'): Dre
    {
        $periods = [];
        
        foreach ($periodsData as $periodData) {
            $data = new GenerateDreData(
                periodType: $periodData['period_type'],
                yearMonth: $periodData['year_month'] ?? '',
                year: $periodData['year'] ?? '',
                quarter: $periodData['quarter'] ?? 0,
                scenario: $scenario
            );
            
            $periods[] = $this->createDrePeriod($data);
        }

        return $this->dreGenerator->generateConsolidated($periods, $categoryId, $scenario);
    }

    public function handleComparative(GenerateDreData $currentData, GenerateDreData $previousData, ?string $categoryId = null): Dre
    {
        $currentPeriod = $this->createDrePeriod($currentData);
        $previousPeriod = $this->createDrePeriod($previousData);

        return $this->dreGenerator->generateComparative($currentPeriod, $previousPeriod, $categoryId);
    }

    public function handleProjected(GenerateDreData $data, int $historicalMonths = 12): Dre
    {
        $period = $this->createDrePeriod($data);

        return $this->dreGenerator->generateProjected($period, $historicalMonths, $data->scenario);
    }

    public function handleByCategory(GenerateDreData $data, string $categoryType): Dre
    {
        $period = $this->createDrePeriod($data);

        return $this->dreGenerator->generateByCategory($period, $categoryType);
    }

    public function handleVarianceAnalysis(GenerateDreData $data, Dre $budgetDre, Dre $actualDre): Dre
    {
        $period = $this->createDrePeriod($data);

        return $this->dreGenerator->generateVarianceAnalysis($period, $budgetDre, $actualDre);
    }

    public function handleTrendAnalysis(array $periodsData, ?string $categoryId = null): Dre
    {
        $periods = [];
        
        foreach ($periodsData as $periodData) {
            $data = new GenerateDreData(
                periodType: $periodData['period_type'],
                yearMonth: $periodData['year_month'] ?? '',
                year: $periodData['year'] ?? '',
                quarter: $periodData['quarter'] ?? 0
            );
            
            $periods[] = $this->createDrePeriod($data);
        }

        return $this->dreGenerator->generateTrendAnalysis($periods, $categoryId);
    }

    public function handleProfitabilityAnalysis(GenerateDreData $data, string $totalAssets, string $totalEquity): Dre
    {
        $period = $this->createDrePeriod($data);

        return $this->dreGenerator->generateProfitabilityAnalysis(
            $period,
            \App\Domain\Shared\Money::of($totalAssets),
            \App\Domain\Shared\Money::of($totalEquity)
        );
    }
}