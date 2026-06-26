<?php

declare(strict_types=1);

namespace App\Domain\Reports;

use App\Domain\Shared\Money;

final class FinancialReport
{
    /** @var array<FinancialReportItem> */
    private array $items = [];

    public function __construct(
        private readonly ReportPeriod $period,
        private readonly string $title = 'Demonstrativo de Resultados do Exercício',
    ) {}

    public function addItem(FinancialReportItem $item): void
    {
        $this->items[] = $item;
    }

    /** @param array<FinancialReportItem> $items */
    public function addItems(array $items): void
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function getPeriod(): ReportPeriod
    {
        return $this->period;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /** @return array<FinancialReportItem> */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalRevenue(): Money
    {
        $total = Money::zero();
        
        foreach ($this->items as $item) {
            if ($item->isRevenue() && !$item->isProfitItem()) {
                $total = $total->add($item->getAmount());
            }
        }
        
        return $total;
    }

    public function getTotalExpenses(): Money
    {
        $total = Money::zero();
        
        foreach ($this->items as $item) {
            if ($item->isExpense()) {
                $total = $total->add($item->getAmount());
            }
        }
        
        return $total;
    }

    public function getGrossProfit(): Money
    {
        $revenue = $this->getTotalRevenue();
        $costOfGoodsSold = $this->getCostOfGoodsSold();
        
        return $revenue->subtract($costOfGoodsSold);
    }

    public function getCostOfGoodsSold(): Money
    {
        $total = Money::zero();
        
        foreach ($this->items as $item) {
            if ($item->getCode() === 'COGS') {
                $total = $total->add($item->getAmount());
            }
        }
        
        return $total;
    }

    public function getOperatingExpenses(): Money
    {
        $total = Money::zero();
        
        foreach ($this->items as $item) {
            if (str_starts_with($item->getCode(), 'E') && $item->getCode() !== 'COGS') {
                $total = $total->add($item->getAmount());
            }
        }
        
        return $total;
    }

    public function getOperatingProfit(): Money
    {
        $grossProfit = $this->getGrossProfit();
        $operatingExpenses = $this->getOperatingExpenses();
        
        return $grossProfit->subtract($operatingExpenses);
    }

    public function getNetProfit(): Money
    {
        $operatingProfit = $this->getOperatingProfit();
        
        // Para simplificar, consideramos que não há itens não operacionais nem impostos
        // Em uma implementação real, esses valores seriam calculados
        return $operatingProfit;
    }

    public function getRevenueByCategory(): array
    {
        $revenueByCategory = [];
        
        foreach ($this->items as $item) {
            if ($item->isRevenue() && !$item->isProfitItem() && $item->getCategoryId() !== null) {
                $categoryId = $item->getCategoryId();
                $categoryName = $item->getCategoryName() ?? 'Sem categoria';
                
                if (!isset($revenueByCategory[$categoryId])) {
                    $revenueByCategory[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'amount' => Money::zero(),
                    ];
                }
                
                $revenueByCategory[$categoryId]['amount'] = $revenueByCategory[$categoryId]['amount']->add($item->getAmount());
            }
        }
        
        return array_values($revenueByCategory);
    }

    public function getExpensesByCategory(): array
    {
        $expensesByCategory = [];
        
        foreach ($this->items as $item) {
            if ($item->isExpense() && $item->getCategoryId() !== null) {
                $categoryId = $item->getCategoryId();
                $categoryName = $item->getCategoryName() ?? 'Sem categoria';
                
                if (!isset($expensesByCategory[$categoryId])) {
                    $expensesByCategory[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'amount' => Money::zero(),
                    ];
                }
                
                $expensesByCategory[$categoryId]['amount'] = $expensesByCategory[$categoryId]['amount']->add($item->getAmount());
            }
        }
        
        return array_values($expensesByCategory);
    }

    public function getSummary(): array
    {
        return [
            'period' => $this->period->toString(),
            'title' => $this->title,
            'total_revenue' => $this->getTotalRevenue()->toNumeric(),
            'total_expenses' => $this->getTotalExpenses()->toNumeric(),
            'gross_profit' => $this->getGrossProfit()->toNumeric(),
            'operating_expenses' => $this->getOperatingExpenses()->toNumeric(),
            'operating_profit' => $this->getOperatingProfit()->toNumeric(),
            'net_profit' => $this->getNetProfit()->toNumeric(),
            'item_count' => count($this->items),
        ];
    }

    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }
        
        return [
            'period' => $this->period->toString(),
            'title' => $this->title,
            'items' => $items,
            'summary' => $this->getSummary(),
        ];
    }
}