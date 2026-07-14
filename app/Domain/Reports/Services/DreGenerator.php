<?php

declare(strict_types=1);

namespace App\Domain\Reports\Services;

use App\Domain\Reports\Dre;
use App\Domain\Reports\DreRepositoryInterface;
use App\Domain\Reports\ValueObjects\DrePeriod;
use App\Domain\Shared\Money;

final readonly class DreGenerator
{
    public function __construct(
        private DreRepositoryInterface $dreRepository,
    ) {}

    public function generate(DrePeriod $period, ?string $categoryId = null, string $scenario = 'base'): Dre
    {
        return $this->dreRepository->generate($period, $categoryId, $scenario);
    }

    public function generateConsolidated(array $periods, ?string $categoryId = null, string $scenario = 'base'): Dre
    {
        if (empty($periods)) {
            throw new \InvalidArgumentException('At least one period must be provided.');
        }

        return $this->dreRepository->generateConsolidated($periods, $categoryId, $scenario);
    }

    public function generateComparative(DrePeriod $currentPeriod, DrePeriod $previousPeriod, ?string $categoryId = null): Dre
    {
        return $this->dreRepository->generateComparative($currentPeriod, $previousPeriod, $categoryId);
    }

    public function generateProjected(DrePeriod $period, int $historicalMonths = 12, string $scenario = 'base'): Dre
    {
        if ($historicalMonths < 1) {
            throw new \InvalidArgumentException('Historical months must be at least 1.');
        }

        return $this->dreRepository->generateProjected($period, $historicalMonths, $scenario);
    }

    public function generateByCategory(DrePeriod $period, string $categoryType): Dre
    {
        $validCategoryTypes = ['revenue', 'expense'];
        if (!in_array($categoryType, $validCategoryTypes, true)) {
            throw new \InvalidArgumentException('Invalid category type. Must be one of: ' . implode(', ', $validCategoryTypes));
        }

        return $this->dreRepository->generateByCategory($period, $categoryType);
    }

    public function generateVarianceAnalysis(DrePeriod $period, Dre $budgetDre, Dre $actualDre): Dre
    {
        return $this->dreRepository->generateVarianceAnalysis($period, $budgetDre, $actualDre);
    }

    public function generateTrendAnalysis(array $periods, ?string $categoryId = null): Dre
    {
        if (empty($periods)) {
            throw new \InvalidArgumentException('At least one period must be provided.');
        }

        return $this->dreRepository->generateTrendAnalysis($periods, $categoryId);
    }

    public function generateProfitabilityAnalysis(DrePeriod $period, Money $totalAssets, Money $totalEquity): Dre
    {
        if ($totalAssets->isNegative()) {
            throw new \InvalidArgumentException('Total assets cannot be negative.');
        }

        if ($totalEquity->isNegative()) {
            throw new \InvalidArgumentException('Total equity cannot be negative.');
        }

        return $this->dreRepository->generateProfitabilityAnalysis($period, $totalAssets, $totalEquity);
    }

    public function export(Dre $dre, string $format): string
    {
        $validFormats = ['pdf', 'excel', 'csv', 'json'];
        if (!in_array($format, $validFormats, true)) {
            throw new \InvalidArgumentException('Invalid export format. Must be one of: ' . implode(', ', $validFormats));
        }

        return $this->dreRepository->export($dre, $format);
    }

    public function save(Dre $dre): void
    {
        $this->dreRepository->save($dre);
    }

    public function findById(string $id): ?Dre
    {
        return $this->dreRepository->findById($id);
    }

    public function findByPeriod(DrePeriod $period, ?string $scenario = null): array
    {
        return $this->dreRepository->findByPeriod($period, $scenario);
    }

    public function delete(string $id): void
    {
        $this->dreRepository->delete($id);
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return $this->dreRepository->findAll($limit, $offset);
    }

    public function getStandardDreStructure(): array
    {
        return [
            [
                'code' => 'REV',
                'description' => 'RECEITA OPERACIONAL BRUTA',
                'type' => 'revenue',
                'level' => 1,
            ],
            [
                'code' => 'REV-DED',
                'description' => '(-) Deduções da Receita',
                'type' => 'revenue',
                'level' => 2,
                'parent_code' => 'REV',
            ],
            [
                'code' => 'REV-NET',
                'description' => 'RECEITA OPERACIONAL LÍQUIDA',
                'type' => 'revenue',
                'level' => 1,
            ],
            [
                'code' => 'CMV',
                'description' => '(-) Custo das Mercadorias Vendidas/Serviços Prestados',
                'type' => 'expense',
                'level' => 1,
            ],
            [
                'code' => 'GP',
                'description' => 'LUCRO BRUTO',
                'type' => 'profit',
                'level' => 1,
            ],
            [
                'code' => 'OP-EXP',
                'description' => '(-) Despesas Operacionais',
                'type' => 'expense',
                'level' => 1,
            ],
            [
                'code' => 'OP-PROF',
                'description' => 'LUCRO OPERACIONAL',
                'type' => 'profit',
                'level' => 1,
            ],
            [
                'code' => 'NON-OP',
                'description' => 'Resultado Não Operacional',
                'type' => 'profit',
                'level' => 1,
            ],
            [
                'code' => 'EBT',
                'description' => 'LUCRO ANTES DOS IMPOSTOS',
                'type' => 'profit',
                'level' => 1,
            ],
            [
                'code' => 'TAX',
                'description' => '(-) Impostos sobre o Lucro',
                'type' => 'expense',
                'level' => 1,
            ],
            [
                'code' => 'NET',
                'description' => 'LUCRO LÍQUIDO',
                'type' => 'profit',
                'level' => 1,
            ],
        ];
    }

    public function calculateFinancialRatios(Dre $dre): array
    {
        $totalRevenue = $dre->getTotalRevenue();
        $totalExpenses = $dre->getTotalExpenses();
        $netProfit = $dre->getNetProfit();
        $grossProfit = $dre->getGrossProfit();
        $operatingProfit = $dre->getOperatingProfit();

        $ratios = [];

        // Margens
        $ratios['gross_margin'] = $this->calculateMargin($grossProfit, $totalRevenue);
        $ratios['operating_margin'] = $this->calculateMargin($operatingProfit, $totalRevenue);
        $ratios['net_margin'] = $this->calculateMargin($netProfit, $totalRevenue);

        // Eficiência
        $ratios['expense_ratio'] = $this->calculateMargin($totalExpenses, $totalRevenue);

        // Rentabilidade
        $ratios['return_on_sales'] = $this->calculateMargin($netProfit, $totalRevenue);

        return $ratios;
    }

    private function calculateMargin(Money $profit, Money $revenue): float
    {
        if ($revenue->isZero()) {
            return 0.0;
        }

        return (float) bcdiv($profit->getAmount(), $revenue->getAmount(), 4);
    }

    public function generateDreTitle(DrePeriod $period, ?string $categoryName = null, ?string $scenario = 'base'): string
    {
        $title = 'Demonstrativo de Resultados do Exercício';

        if ($categoryName) {
            $title .= " - {$categoryName}";
        }

        $title .= " - {$period->getLabel()}";

        if ($scenario !== 'base') {
            $scenarioLabels = [
                'optimistic' => 'Cenário Otimista',
                'pessimistic' => 'Cenário Pessimista',
                'custom' => 'Cenário Personalizado',
            ];
            
            if (isset($scenarioLabels[$scenario])) {
                $title .= " ({$scenarioLabels[$scenario]})";
            }
        }

        return $title;
    }
}