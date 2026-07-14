<?php

declare(strict_types=1);

namespace App\Domain\BankReconciliation;

interface ReconciliationRepositoryInterface
{
    /** @return array<ReconciliationItem> */
    public function findPendingByBankAccountId(string $bankAccountId): array;
    
    /** @return array<ReconciliationItem> */
    public function findReconciledByBankAccountId(string $bankAccountId): array;
    
    /** @return array<ReconciliationItem> */
    public function findDiscrepanciesByBankAccountId(string $bankAccountId): array;
    
    public function save(ReconciliationItem $item): void;
    
    public function delete(string $id): void;
    
    public function findById(string $id): ?ReconciliationItem;
    
    public function generateSummary(string $bankAccountId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): ReconciliationSummary;
    
    /** @return array<ReconciliationSummary> */
    public function getRecentSummaries(string $bankAccountId, int $limit = 5): array;
    
    public function markAsReconciled(string $id, string $transactionId): void;
    
    public function markAsUnreconciled(string $id): void;
    
    public function updateNotes(string $id, string $notes): void;
}