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

    public function generateCashFlowProjection(ProjectionPeriod $period): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::CASH_FLOW,
            title: 'Projeção de Fluxo de Caixa',
        );

        // Calcular fluxo de caixa baseado em receitas e despesas projetadas
        $revenueProjection = $this->generateRevenueProjection($period);
        $expenseProjection = $this->generateExpenseProjection($period);

        // Consolidar itens de fluxo de caixa
        foreach ($revenueProjection->getItems() as $item) {
            $projection->addItem(new ProjectionItem(
                id: 'cf-rev-' . $item->getId(),
                date: $item->getDate(),
                description: 'Receita: ' . $item->getDescription(),
                amount: $item->getAmount(),
                type: ProjectionType::CASH_FLOW,
                source: 'cash_flow_revenue',
            ));
        }

        foreach ($expenseProjection->getItems() as $item) {
            $projection->addItem(new ProjectionItem(
                id: 'cf-exp-' . $item->getId(),
                date: $item->getDate(),
                description: 'Despesa: ' . $item->getDescription(),
                amount: $item->getAmount()->multiply('-1'),
                type: ProjectionType::CASH_FLOW,
                source: 'cash_flow_expense',
            ));
        }

        return $projection;
    }

    public function generateBalanceSheetProjection(ProjectionPeriod $period): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::BALANCE_SHEET,
            title: 'Projeção de Balanço Patrimonial',
        );

        // Projetar ativos
        $projectedAssets = $this->projectAssets($period);
        foreach ($projectedAssets as $asset) {
            $projection->addItem(new ProjectionItem(
                id: 'asset-' . uniqid(),
                date: $period->getEndDate(),
                description: $asset['description'],
                amount: $asset['amount'],
                type: ProjectionType::BALANCE_SHEET,
                source: 'projected_assets',
            ));
        }

        // Projetar passivos
        $projectedLiabilities = $this->projectLiabilities($period);
        foreach ($projectedLiabilities as $liability) {
            $projection->addItem(new ProjectionItem(
                id: 'liab-' . uniqid(),
                date: $period->getEndDate(),
                description: $liability['description'],
                amount: $liability['amount']->multiply('-1'),
                type: ProjectionType::BALANCE_SHEET,
                source: 'projected_liabilities',
            ));
        }

        // Projetar patrimônio líquido
        $projectedEquity = $this->projectEquity($period);
        foreach ($projectedEquity as $equity) {
            $projection->addItem(new ProjectionItem(
                id: 'equity-' . uniqid(),
                date: $period->getEndDate(),
                description: $equity['description'],
                amount: $equity['amount'],
                type: ProjectionType::BALANCE_SHEET,
                source: 'projected_equity',
            ));
        }

        return $projection;
    }

    /**
     * @return array<array{description: string, amount: Money}>
     */
    private function projectAssets(ProjectionPeriod $period): array
    {
        // Implementação simplificada - em produção, buscar dados reais
        return [
            ['description' => 'Caixa e Equivalentes', 'amount' => Money::of('50000.00')],
            ['description' => 'Contas a Receber', 'amount' => Money::of('75000.00')],
            ['description' => 'Estoques', 'amount' => Money::of('30000.00')],
            ['description' => 'Imobilizado', 'amount' => Money::of('200000.00')],
        ];
    }

    /**
     * @return array<array{description: string, amount: Money}>
     */
    private function projectLiabilities(ProjectionPeriod $period): array
    {
        // Implementação simplificada - em produção, buscar dados reais
        return [
            ['description' => 'Fornecedores', 'amount' => Money::of('25000.00')],
            ['description' => 'Empréstimos Bancários', 'amount' => Money::of('100000.00')],
            ['description' => 'Obrigações Fiscais', 'amount' => Money::of('15000.00')],
        ];
    }

    /**
     * @return array<array{description: string, amount: Money}>
     */
    private function projectEquity(ProjectionPeriod $period): array
    {
        // Implementação simplificada - em produção, buscar dados reais
        return [
            ['description' => 'Capital Social', 'amount' => Money::of('150000.00')],
            ['description' => 'Reservas de Lucro', 'amount' => Money::of('50000.00')],
            ['description' => 'Lucros Acumulados', 'amount' => Money::of('80000.00')],
        ];
    }
}
