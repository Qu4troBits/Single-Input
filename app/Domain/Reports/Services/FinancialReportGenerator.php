<?php

declare(strict_types=1);

namespace App\Domain\Reports\Services;

use App\Domain\Reports\FinancialReport;
use App\Domain\Reports\FinancialReportItem;
use App\Domain\Reports\ReportPeriod;
use App\Domain\Shared\Money;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;

final readonly class FinancialReportGenerator
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function generateDre(ReportPeriod $period): FinancialReport
    {
        $report = new FinancialReport($period);
        
        // Obter transações do período
        $transactions = $this->transactionRepository->findByPeriod(
            $period->getStartDate(),
            $period->getEndDate()
        );
        
        // Agrupar por categoria e direção
        $revenueByCategory = [];
        $expensesByCategory = [];
        
        foreach ($transactions as $transaction) {
            $categoryId = $transaction->getCategoryId()->toString();
            $category = $this->categoryRepository->findById($transaction->getCategoryId());
            $categoryName = $category?->getName() ?? 'Desconhecida';
            
            if ($transaction->getDirection()->isIncome()) {
                if (!isset($revenueByCategory[$categoryId])) {
                    $revenueByCategory[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'amount' => Money::zero(),
                    ];
                }
                $revenueByCategory[$categoryId]['amount'] = $revenueByCategory[$categoryId]['amount']->add($transaction->getAmount());
            } else {
                if (!isset($expensesByCategory[$categoryId])) {
                    $expensesByCategory[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'amount' => Money::zero(),
                    ];
                }
                $expensesByCategory[$categoryId]['amount'] = $expensesByCategory[$categoryId]['amount']->add($transaction->getAmount());
            }
        }
        
        // Adicionar receitas ao relatório
        $revenueCode = 1;
        foreach ($revenueByCategory as $revenue) {
            $report->addItem(FinancialReportItem::createRevenueItem(
                code: 'R' . str_pad((string) $revenueCode, 2, '0', STR_PAD_LEFT),
                description: $revenue['category_name'],
                amount: $revenue['amount'],
                categoryId: $revenue['category_id'],
                categoryName: $revenue['category_name'],
            ));
            $revenueCode++;
        }
        
        // Adicionar custo das mercadorias vendidas (COGS)
        $cogsAmount = $this->calculateCostOfGoodsSold($period);
        if (!$cogsAmount->isZero()) {
            $report->addItem(FinancialReportItem::createExpenseItem(
                code: 'COGS',
                description: 'Custo das Mercadorias Vendidas',
                amount: $cogsAmount,
            ));
        }
        
        // Adicionar lucro bruto
        if ($report->getTotalRevenue()->isGreaterThan(Money::zero())) {
            $report->addItem(FinancialReportItem::createGrossProfitItem(
                $report->getTotalRevenue(),
                $report->getCostOfGoodsSold()
            ));
        }
        
        // Adicionar despesas operacionais
        $expenseCode = 1;
        foreach ($expensesByCategory as $expense) {
            $report->addItem(FinancialReportItem::createExpenseItem(
                code: 'E' . str_pad((string) $expenseCode, 2, '0', STR_PAD_LEFT),
                description: $expense['category_name'],
                amount: $expense['amount'],
                categoryId: $expense['category_id'],
                categoryName: $expense['category_name'],
            ));
            $expenseCode++;
        }
        
        // Adicionar lucro operacional
        if ($report->getGrossProfit()->isGreaterThan(Money::zero())) {
            $report->addItem(FinancialReportItem::createOperatingProfitItem(
                $report->getGrossProfit(),
                $report->getOperatingExpenses()
            ));
        }
        
        // Adicionar lucro líquido
        if ($report->getOperatingProfit()->isGreaterThan(Money::zero())) {
            $report->addItem(FinancialReportItem::createNetProfitItem(
                $report->getOperatingProfit(),
                Money::zero(), // Itens não operacionais (simplificado)
                Money::zero(), // Impostos (simplificado)
            ));
        }
        
        return $report;
    }
    
    private function calculateCostOfGoodsSold(ReportPeriod $period): Money
    {
        // Em uma implementação real, isso seria calculado com base em:
        // 1. Estoque inicial
        // 2. Compras no período
        // 3. Estoque final
        // Por enquanto, retornamos zero para simplificar
        
        return Money::zero();
    }
    
    public function generateComparativeDre(ReportPeriod $currentPeriod, ReportPeriod $previousPeriod): array
    {
        $currentReport = $this->generateDre($currentPeriod);
        $previousReport = $this->generateDre($previousPeriod);
        
        return [
            'current_period' => $currentReport->toArray(),
            'previous_period' => $previousReport->toArray(),
            'comparison' => [
                'revenue_change' => $this->calculatePercentageChange(
                    $previousReport->getTotalRevenue(),
                    $currentReport->getTotalRevenue()
                ),
                'expense_change' => $this->calculatePercentageChange(
                    $previousReport->getTotalExpenses(),
                    $currentReport->getTotalExpenses()
                ),
                'net_profit_change' => $this->calculatePercentageChange(
                    $previousReport->getNetProfit(),
                    $currentReport->getNetProfit()
                ),
            ],
        ];
    }
    
    private function calculatePercentageChange(Money $previous, Money $current): array
    {
        if ($previous->isZero()) {
            return [
                'amount' => $current->toNumeric(),
                'percentage' => $current->isZero() ? '0.00' : '100.00',
                'direction' => $current->isZero() ? 'neutral' : ($current->isGreaterThan(Money::zero()) ? 'increase' : 'decrease'),
            ];
        }
        
        $changeAmount = $current->subtract($previous);
        $percentage = $changeAmount->divide($previous->toNumeric())->multiply('100');
        
        return [
            'amount' => $changeAmount->toNumeric(),
            'percentage' => $percentage->toNumeric(),
            'direction' => $changeAmount->isZero() ? 'neutral' : ($changeAmount->isGreaterThan(Money::zero()) ? 'increase' : 'decrease'),
        ];
    }
}