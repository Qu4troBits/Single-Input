<?php

declare(strict_types=1); 

namespace App\Domain\BankReconciliation;

use App\Domain\Shared\Money;

final readonly class ReconciliationSummary
{
    public function __construct(
        private string $bankAccountId,
        private \DateTimeImmutable $periodStart,
        private \DateTimeImmutable $periodEnd,
        private Money $openingBalance,
        private Money $closingBalance,
        private Money $totalCredits,
        private Money $totalDebits,
        private Money $expectedBalance,
        private Money $actualBalance,
        private int $pendingItems,
        private int $reconciledItems,
        private int $discrepancyItems,
        private \DateTimeImmutable $generatedAt,
    ) {}

    public function getBankAccountId(): string
    {
        return $this->bankAccountId;
    }

    public function getPeriodStart(): \DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): \DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function getOpeningBalance(): Money
    {
        return $this->openingBalance;
    }

    public function getClosingBalance(): Money
    {
        return $this->closingBalance;
    }

    public function getTotalCredits(): Money
    {
        return $this->totalCredits;
    }

    public function getTotalDebits(): Money
    {
        return $this->totalDebits;
    }

    public function getExpectedBalance(): Money
    {
        return $this->expectedBalance;
    }

    public function getActualBalance(): Money
    {
        return $this->actualBalance;
    }

    public function getBalanceDifference(): Money
    {
        return $this->actualBalance->subtract($this->expectedBalance);
    }

    public function getPendingItems(): int
    {
        return $this->pendingItems;
    }

    public function getReconciledItems(): int
    {
        return $this->reconciledItems;
    }

    public function getDiscrepancyItems(): int
    {
        return $this->discrepancyItems;
    }

    public function getGeneratedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function getReconciliationRate(): float
    {
        $totalItems = $this->pendingItems + $this->reconciledItems + $this->discrepancyItems;
        
        if ($totalItems === 0) {
            return 0.0;
        }
        
        return ($this->reconciledItems / $totalItems) * 100;
    }

    public function isBalanced(): bool
    {
        return $this->getBalanceDifference()->isZero();
    }

    public function getDiscrepancyAmount(): Money
    {
        return $this->getBalanceDifference()->abs();
    }

    public function toArray(): array
    {
        return [
            'bank_account_id' => $this->bankAccountId,
            'period_start' => $this->periodStart->format('Y-m-d'),
            'period_end' => $this->periodEnd->format('Y-m-d'),
            'opening_balance' => $this->openingBalance->toNumeric(),
            'closing_balance' => $this->closingBalance->toNumeric(),
            'total_credits' => $this->totalCredits->toNumeric(),
            'total_debits' => $this->totalDebits->toNumeric(),
            'expected_balance' => $this->expectedBalance->toNumeric(),
            'actual_balance' => $this->actualBalance->toNumeric(),
            'balance_difference' => $this->getBalanceDifference()->toNumeric(),
            'pending_items' => $this->pendingItems,
            'reconciled_items' => $this->reconciledItems,
            'discrepancy_items' => $this->discrepancyItems,
            'reconciliation_rate' => round($this->getReconciliationRate(), 2),
            'is_balanced' => $this->isBalanced(),
            'discrepancy_amount' => $this->getDiscrepancyAmount()->toNumeric(),
            'generated_at' => $this->generatedAt->format('Y-m-d H:i:s'),
        ];
    }
}