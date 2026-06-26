<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\BankReconciliation\ReconciliationItem;
use App\Domain\BankReconciliation\ReconciliationRepositoryInterface;
use App\Domain\BankReconciliation\ReconciliationStatus;
use App\Domain\BankReconciliation\ReconciliationSummary;
use App\Domain\Shared\Money;
use App\Infrastructure\Persistence\Eloquent\Models\ReconciliationItemModel;
use Illuminate\Support\Collection;

final class EloquentReconciliationRepository implements ReconciliationRepositoryInterface
{
    public function findPendingByBankAccountId(string $bankAccountId): array
    {
        $models = ReconciliationItemModel::query()
            ->where('bank_account_id', $bankAccountId)
            ->where('status', ReconciliationStatus::PENDING->value)
            ->orderBy('date')
            ->get();

        return $this->mapModelsToDomain($models);
    }

    public function save(ReconciliationItem $item): void
    {
        ReconciliationItemModel::updateOrCreate(
            ['id' => $item->getId()],
            [
                'bank_account_id' => $item->getBankAccountId(),
                'date' => $item->getDate(),
                'description' => $item->getDescription(),
                'amount' => $item->getAmount()->getAmount(),
                'status' => $item->getStatus()->value,
                'transaction_id' => $item->getTransactionId(),
                'bank_statement_id' => $item->getBankStatementId(),
                'notes' => $item->getNotes(),
            ]
        );
    }

    public function generateSummary(string $bankAccountId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): ReconciliationSummary
    {
        $items = ReconciliationItemModel::query()
            ->where('bank_account_id', $bankAccountId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalCredits = Money::zero();
        $totalDebits = Money::zero();
        $pendingItems = 0;
        $reconciledItems = 0;
        $discrepancyItems = 0;

        foreach ($items as $item) {
            $amount = Money::of($item->amount);
            
            if ($item->status === ReconciliationStatus::PENDING->value) {
                $pendingItems++;
            } elseif ($item->status === ReconciliationStatus::RECONCILED->value) {
                $reconciledItems++;
            } elseif ($item->status === ReconciliationStatus::DISCREPANCY->value) {
                $discrepancyItems++;
            }

            // Determinar se é crédito ou débito baseado no tipo da transação associada
            // Esta lógica pode ser refinada conforme necessário
            if ($item->transaction && $item->transaction->direction === 'income') {
                $totalCredits = $totalCredits->add($amount);
            } else {
                $totalDebits = $totalDebits->add($amount);
            }
        }

        // Calcular saldos esperados e reais
        // Esta é uma implementação simplificada - pode ser expandida conforme necessário
        $openingBalance = Money::zero(); // Seria obtido do extrato anterior
        $expectedBalance = $openingBalance->add($totalCredits)->subtract($totalDebits);
        $actualBalance = $expectedBalance; // Em uma implementação real, viria do extrato bancário

        return new ReconciliationSummary(
            bankAccountId: $bankAccountId,
            periodStart: $startDate,
            periodEnd: $endDate,
            openingBalance: $openingBalance,
            closingBalance: $expectedBalance,
            totalCredits: $totalCredits,
            totalDebits: $totalDebits,
            expectedBalance: $expectedBalance,
            actualBalance: $actualBalance,
            pendingItems: $pendingItems,
            reconciledItems: $reconciledItems,
            discrepancyItems: $discrepancyItems,
            generatedAt: new \DateTimeImmutable(),
        );
    }

    /**
     * @param Collection<ReconciliationItemModel> $models
     * @return array<ReconciliationItem>
     */
    private function mapModelsToDomain(Collection $models): array
    {
        $items = [];

        foreach ($models as $model) {
            $items[] = new ReconciliationItem(
                id: $model->id,
                bankAccountId: $model->bank_account_id,
                date: \DateTimeImmutable::createFromInterface($model->date),
                description: $model->description,
                amount: Money::of($model->amount),
                status: ReconciliationStatus::from($model->status),
                transactionId: $model->transaction_id,
                bankStatementId: $model->bank_statement_id,
                notes: $model->notes,
            );
        }

        return $items;
    }
}