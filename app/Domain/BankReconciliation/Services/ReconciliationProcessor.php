<?php

declare(strict_types=1);

namespace App\Domain\BankReconciliation\Services;

use App\Domain\BankReconciliation\ReconciliationItem;
use App\Domain\BankReconciliation\ReconciliationStatus;
use App\Domain\BankReconciliation\ReconciliationSummary;
use App\Domain\Shared\Money;
use App\Domain\Transactions\TransactionRepositoryInterface;

final readonly class ReconciliationProcessor
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function processReconciliation(
        array $pendingItems,
        array $bankStatementItems
    ): array {
        $reconciledItems = [];
        $discrepancies = [];
        
        // Mapear transações pendentes por data e valor
        $pendingByKey = [];
        foreach ($pendingItems as $item) {
            $key = $this->createItemKey($item->getDate(), $item->getAmount());
            $pendingByKey[$key][] = $item;
        }
        
        // Processar itens do extrato bancário
        foreach ($bankStatementItems as $bankItem) {
            $key = $this->createItemKey($bankItem->getDate(), $bankItem->getAmount());
            
            if (isset($pendingByKey[$key]) && !empty($pendingByKey[$key])) {
                // Encontrou correspondência
                $pendingItem = array_shift($pendingByKey[$key]);
                
                $reconciledItems[] = $pendingItem->markAsReconciled(
                    $pendingItem->getTransactionId() ?? $bankItem->getId()
                );
                
                // Remover chave se não houver mais itens pendentes
                if (empty($pendingByKey[$key])) {
                    unset($pendingByKey[$key]);
                }
            } else {
                // Não encontrou correspondência - item não reconhecido
                $discrepancies[] = $bankItem;
            }
        }
        
        // Itens pendentes que não foram encontrados no extrato
        $remainingPending = [];
        foreach ($pendingByKey as $items) {
            foreach ($items as $item) {
                $remainingPending[] = $item;
            }
        }
        
        return [
            'reconciled_items' => $reconciledItems,
            'discrepancies' => $discrepancies,
            'remaining_pending' => $remainingPending,
        ];
    }
    
    public function generateSummary(
        string $bankAccountId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array $reconciledItems,
        array $pendingItems,
        array $discrepancies
    ): ReconciliationSummary {
        // Calcular saldo inicial (do período anterior)
        $openingBalance = $this->calculateOpeningBalance($bankAccountId, $startDate);
        
        // Calcular totais de créditos e débitos
        $totalCredits = Money::zero();
        $totalDebits = Money::zero();
        
        foreach ($reconciledItems as $item) {
            $amount = $item->getAmount();
            if ($amount->isGreaterThan(Money::zero())) {
                $totalCredits = $totalCredits->add($amount);
            } else {
                $totalDebits = $totalDebits->add($amount->abs());
            }
        }
        
        // Calcular saldo esperado
        $expectedBalance = $openingBalance
            ->add($totalCredits)
            ->subtract($totalDebits);
        
        // Calcular saldo atual (do extrato bancário)
        $actualBalance = $this->calculateActualBalance($bankAccountId, $endDate);
        
        // Contar itens por status
        $pendingCount = count($pendingItems);
        $reconciledCount = count($reconciledItems);
        $discrepancyCount = count($discrepancies);
        
        return new ReconciliationSummary(
            bankAccountId: $bankAccountId,
            periodStart: $startDate,
            periodEnd: $endDate,
            openingBalance: $openingBalance,
            closingBalance: $actualBalance,
            totalCredits: $totalCredits,
            totalDebits: $totalDebits,
            expectedBalance: $expectedBalance,
            actualBalance: $actualBalance,
            pendingItems: $pendingCount,
            reconciledItems: $reconciledCount,
            discrepancyItems: $discrepancyCount,
            generatedAt: new \DateTimeImmutable(),
        );
    }
    
    private function createItemKey(\DateTimeImmutable $date, Money $amount): string
    {
        return sprintf(
            '%s-%s',
            $date->format('Ymd'),
            $amount->toNumeric()
        );
    }
    
    private function calculateOpeningBalance(string $bankAccountId, \DateTimeImmutable $startDate): Money
    {
        // Em uma implementação real, isso buscaria o saldo do período anterior
        // Por enquanto, retornamos zero para simplificar
        return Money::zero();
    }
    
    private function calculateActualBalance(string $bankAccountId, \DateTimeImmutable $endDate): Money
    {
        // Em uma implementação real, isso buscaria o saldo atual do extrato bancário
        // Por enquanto, retornamos zero para simplificar
        return Money::zero();
    }
    
    public function identifyDiscrepancies(
        array $systemItems,
        array $bankItems
    ): array {
        $discrepancies = [];
        
        $systemByKey = [];
        foreach ($systemItems as $item) {
            $key = $this->createItemKey($item->getDate(), $item->getAmount());
            $systemByKey[$key] = $item;
        }
        
        $bankByKey = [];
        foreach ($bankItems as $item) {
            $key = $this->createItemKey($item->getDate(), $item->getAmount());
            $bankByKey[$key] = $item;
        }
        
        // Itens no sistema que não estão no extrato
        foreach ($systemByKey as $key => $systemItem) {
            if (!isset($bankByKey[$key])) {
                $discrepancies[] = [
                    'type' => 'missing_in_bank',
                    'item' => $systemItem,
                    'description' => 'Transação registrada no sistema não encontrada no extrato bancário',
                ];
            }
        }
        
        // Itens no extrato que não estão no sistema
        foreach ($bankByKey as $key => $bankItem) {
            if (!isset($systemByKey[$key])) {
                $discrepancies[] = [
                    'type' => 'missing_in_system',
                    'item' => $bankItem,
                    'description' => 'Transação no extrato bancário não registrada no sistema',
                ];
            }
        }
        
        return $discrepancies;
    }
}