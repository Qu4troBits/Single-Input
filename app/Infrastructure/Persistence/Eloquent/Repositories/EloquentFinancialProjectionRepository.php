<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\FinancialProjections\FinancialProjection;
use App\Domain\FinancialProjections\FinancialProjectionRepositoryInterface;
use App\Domain\FinancialProjections\PeriodType;
use App\Domain\FinancialProjections\ProjectionItem;
use App\Domain\FinancialProjections\ProjectionPeriod;
use App\Domain\FinancialProjections\ProjectionType;
use App\Domain\Shared\Money;
use App\Infrastructure\Persistence\Eloquent\Models\FinancialProjectionModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProjectionItemModel;
use Illuminate\Support\Collection;

final class EloquentFinancialProjectionRepository implements FinancialProjectionRepositoryInterface
{
    public function generateRevenueProjection(ProjectionPeriod $period, ?string $categoryId = null): FinancialProjection
    {
        // Em uma implementação real, isso usaria dados históricos e fórmulas
        // Esta é uma implementação simplificada
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::REVENUE,
            title: 'Projeção de Receitas - ' . $period->getLabel(),
            categoryId: $categoryId,
        );

        // Adicionar itens de exemplo
        $projection->addItem(new ProjectionItem(
            id: 'rev-proj-1',
            date: $period->getStartDate(),
            description: 'Receita Projetada - Vendas',
            amount: Money::of('15000.00'),
            type: ProjectionType::REVENUE,
            categoryId: $categoryId,
            source: 'formula',
        ));

        return $projection;
    }

    public function generateExpenseProjection(ProjectionPeriod $period, ?string $categoryId = null): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::EXPENSE,
            title: 'Projeção de Despesas - ' . $period->getLabel(),
            categoryId: $categoryId,
        );

        // Adicionar itens de exemplo
        $projection->addItem(new ProjectionItem(
            id: 'exp-proj-1',
            date: $period->getStartDate(),
            description: 'Despesa Projetada - Salários',
            amount: Money::of('8000.00'),
            type: ProjectionType::EXPENSE,
            categoryId: $categoryId,
            source: 'formula',
        ));

        return $projection;
    }

    public function generateProfitProjection(ProjectionPeriod $period): FinancialProjection
    {
        $revenueProjection = $this->generateRevenueProjection($period);
        $expenseProjection = $this->generateExpenseProjection($period);

        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::PROFIT,
            title: 'Projeção de Lucro - ' . $period->getLabel(),
        );

        // Calcular lucro projetado
        $projectedProfit = $revenueProjection->getTotal()->subtract($expenseProjection->getTotal());

        $projection->addItem(new ProjectionItem(
            id: 'profit-proj-1',
            date: $period->getStartDate(),
            description: 'Lucro Projetado',
            amount: $projectedProfit,
            type: ProjectionType::PROFIT,
            source: 'formula',
        ));

        return $projection;
    }

    public function generateCashFlowProjection(ProjectionPeriod $period): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::CASH_FLOW,
            title: 'Projeção de Fluxo de Caixa - ' . $period->getLabel(),
        );

        // Implementação simplificada
        $projection->addItem(new ProjectionItem(
            id: 'cashflow-proj-1',
            date: $period->getStartDate(),
            description: 'Fluxo de Caixa Projetado',
            amount: Money::of('5000.00'),
            type: ProjectionType::CASH_FLOW,
            source: 'formula',
        ));

        return $projection;
    }

    public function generateBalanceSheetProjection(ProjectionPeriod $period): FinancialProjection
    {
        $projection = new FinancialProjection(
            period: $period,
            type: ProjectionType::BALANCE_SHEET,
            title: 'Projeção de Balanço Patrimonial - ' . $period->getLabel(),
        );

        // Implementação simplificada
        $projection->addItem(new ProjectionItem(
            id: 'balance-proj-1',
            date: $period->getStartDate(),
            description: 'Patrimônio Líquido Projetado',
            amount: Money::of('25000.00'),
            type: ProjectionType::BALANCE_SHEET,
            source: 'formula',
        ));

        return $projection;
    }

    /**
     * @return array<FinancialProjection>
     */
    public function findProjectionsByPeriod(ProjectionPeriod $period): array
    {
        $models = FinancialProjectionModel::query()
            ->where('period_type', $period->getPeriodType()->value)
            ->where(function ($query) use ($period) {
                $query->where('year_month', $period->getStartDate()->format('Y-m'))
                    ->orWhere('year', $period->getStartDate()->format('Y'))
                    ->orWhere(function ($query) use ($period) {
                        $query->where('year', $period->getStartDate()->format('Y'))
                            ->where('quarter', ceil($period->getStartDate()->format('m') / 3));
                    });
            })
            ->with('items')
            ->get();

        return $this->mapModelsToDomain($models);
    }

    public function save(FinancialProjection $projection): void
    {
        $model = FinancialProjectionModel::updateOrCreate(
            ['id' => $projection->toArray()['id'] ?? uniqid('proj-')],
            [
                'type' => $projection->getType()->value,
                'period_type' => $projection->getPeriod()->getPeriodType()->value,
                'year_month' => $projection->getPeriod()->getPeriodType() === PeriodType::MONTHLY 
                    ? $projection->getPeriod()->getStartDate()->format('Y-m') 
                    : null,
                'year' => $projection->getPeriod()->getStartDate()->format('Y'),
                'quarter' => $projection->getPeriod()->getPeriodType() === PeriodType::QUARTERLY
                    ? ceil($projection->getPeriod()->getStartDate()->format('m') / 3)
                    : null,
                'category_id' => $projection->getCategoryId(),
                'scenario' => $projection->getScenario(),
                'title' => $projection->getTitle(),
                'notes' => $projection->toArray()['notes'] ?? null,
            ]
        );

        // Salvar itens
        foreach ($projection->getItems() as $item) {
            ProjectionItemModel::updateOrCreate(
                ['id' => $item->getId()],
                [
                    'projection_id' => $model->id,
                    'date' => $item->getDate(),
                    'description' => $item->getDescription(),
                    'amount' => $item->getAmount()->getAmount(),
                    'type' => $item->getType()->value,
                    'category_id' => $item->getCategoryId(),
                    'category_name' => $item->getCategoryName(),
                    'notes' => $item->getNotes(),
                    'source' => $item->getSource(),
                ]
            );
        }
    }

    public function delete(string $projectionId): void
    {
        FinancialProjectionModel::find($projectionId)?->delete();
    }

    /**
     * @return array<string>
     */
    public function getAvailableScenarios(): array
    {
        return ['base', 'optimistic', 'pessimistic', 'custom'];
    }

    /**
     * @param Collection<FinancialProjectionModel> $models
     * @return array<FinancialProjection>
     */
    private function mapModelsToDomain(Collection $models): array
    {
        $projections = [];

        foreach ($models as $model) {
            $period = match ($model->period_type) {
                'monthly' => ProjectionPeriod::createMonthly($model->year_month),
                'quarterly' => ProjectionPeriod::createQuarterly($model->year, $model->quarter),
                'yearly' => ProjectionPeriod::createYearly($model->year),
                default => throw new \InvalidArgumentException('Invalid period type.'),
            };

            $projection = new FinancialProjection(
                period: $period,
                type: ProjectionType::from($model->type),
                title: $model->title,
                categoryId: $model->category_id,
                scenario: $model->scenario,
            );

            foreach ($model->items as $itemModel) {
                $projection->addItem(new ProjectionItem(
                    id: $itemModel->id,
                    date: \DateTimeImmutable::createFromInterface($itemModel->date),
                    description: $itemModel->description,
                    amount: Money::of($itemModel->amount),
                    type: ProjectionType::from($itemModel->type),
                    categoryId: $itemModel->category_id,
                    categoryName: $itemModel->category_name,
                    notes: $itemModel->notes,
                    source: $itemModel->source,
                ));
            }

            $projections[] = $projection;
        }

        return $projections;
    }
}