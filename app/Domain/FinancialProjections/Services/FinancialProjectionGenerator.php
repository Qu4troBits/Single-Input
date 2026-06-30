<?php

declare(strict_types=1);

namespace App\Domain\FinancialProjections\Services;

use App\Domain\FinancialProjections\FinancialProjection;
use App\Domain\FinancialProjections\FinancialProjectionRepositoryInterface;
use App\Domain\FinancialProjections\PeriodType;
use App\Domain\FinancialProjections\ProjectionItem;
use App\Domain\FinancialProjections\ProjectionPeriod;
use App\Domain\FinancialProjections\ProjectionType;
use App\Domain\Shared\Money;

final readonly class FinancialProjectionGenerator
{
    public function __construct(
        private FinancialProjectionRepositoryInterface $projectionRepository,
    ) {}

    public function generateRevenueProjection(ProjectionPeriod $period, ?string $categoryId = null): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::REVENUE,
            title: 'Projeção de Receitas',
            categoryId: $categoryId,
        );

        // Lógica para gerar projeção de receitas baseada em dados históricos
        // Esta é uma implementação simplificada
        $historicalData = $this->getHistoricalRevenueData($period, $categoryId);
        
        foreach ($historicalData as $item) {
            $projection->addItem(new ProjectionItem(
                id: $item['id'],
                date: $item['date'],
                description: $item['description'],
                amount: Money::of($item['amount']),
                type: ProjectionType::REVENUE,
                categoryId: $categoryId,
                source: 'historical',
            ));
        }

        // Aplicar fatores de crescimento baseados no tipo de período
        $growthFactor = $this->calculateGrowthFactor($period->getPeriodType());
        
        // Adicionar itens projetados
        $projectedItems = $this->generateProjectedItems($historicalData, $growthFactor);
        foreach ($projectedItems as $item) {
            $projection->addItem($item);
        }

        return $projection;
    }

    public function generateExpenseProjection(ProjectionPeriod $period, ?string $categoryId = null): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::EXPENSE,
            title: 'Projeção de Despesas',
            categoryId: $categoryId,
        );

        // Lógica similar para despesas
        // Implementação simplificada

        return $projection;
    }

    public function generateProfitProjection(ProjectionPeriod $period): FinancialProjection
    {
        $revenueProjection = $this->generateRevenueProjection($period);
        $expenseProjection = $this->generateExpenseProjection($period);

        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::PROFIT,
            title: 'Projeção de Lucro',
        );

        // Calcular lucro baseado nas projeções de receita e despesa
        // Implementação simplificada

        return $projection;
    }

    /**
     * @return array<array{id: string, date: \DateTimeImmutable, description: string, amount: string}>
     */
    private function getHistoricalRevenueData(ProjectionPeriod $period, ?string $categoryId = null): array
    {
        // Em uma implementação real, isso buscaria dados históricos do banco
        // Esta é uma implementação de exemplo
        return [
            [
                'id' => 'hist-1',
                'date' => $period->getStartDate()->modify('-1 month'),
                'description' => 'Venda de Produtos',
                'amount' => '10000.00',
            ],
            [
                'id' => 'hist-2',
                'date' => $period->getStartDate()->modify('-2 months'),
                'description' => 'Venda de Serviços',
                'amount' => '8000.00',
            ],
        ];
    }

    private function calculateGrowthFactor(PeriodType $periodType): float
    {
        return match ($periodType) {
            PeriodType::MONTHLY => 1.05, // 5% de crescimento mensal
            PeriodType::QUARTERLY => 1.15, // 15% de crescimento trimestral
            PeriodType::YEARLY => 1.30, // 30% de crescimento anual
        };
    }

    /**
     * @param array<array{id: string, date: \DateTimeImmutable, description: string, amount: string}> $historicalData
     * @return array<ProjectionItem>
     */
    private function generateProjectedItems(array $historicalData, float $growthFactor): array
    {
        $items = [];
        
        // Calcular média histórica
        $totalAmount = Money::zero();
        foreach ($historicalData as $item) {
            $totalAmount = $totalAmount->add(Money::of($item['amount']));
        }
        
        $averageAmount = $totalAmount->divide((string) count($historicalData));
        $projectedAmount = $averageAmount->multiply((string) $growthFactor);

        // Criar item projetado
        $items[] = new ProjectionItem(
            id: 'proj-' . uniqid(),
            date: new \DateTimeImmutable(),
            description: 'Receita Projetada',
            amount: $projectedAmount,
            type: ProjectionType::REVENUE,
            source: 'formula',
        );

        return $items;
    }
}