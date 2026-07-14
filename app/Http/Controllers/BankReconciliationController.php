<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\BankReconciliation\DTOs\ImportBankStatementData;
use App\Application\BankReconciliation\DTOs\ReconcileBankAccountData;
use App\Application\BankReconciliation\Handlers\ImportBankStatementHandler;
use App\Application\BankReconciliation\Handlers\ReconcileBankAccountHandler;
use App\Domain\BankReconciliation\ReconciliationRepositoryInterface;
use App\Domain\BankReconciliation\ReconciliationStatus;
use App\Http\Requests\BankReconciliation\ImportBankStatementRequest;
use App\Http\Requests\BankReconciliation\ReconcileBankAccountRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class BankReconciliationController extends Controller
{
    public function index(ReconciliationRepositoryInterface $repository): Response
    {
        $bankAccounts = \App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $reconciliationSummaries = [];

        foreach ($bankAccounts as $bankAccount) {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->endOfMonth();

            $summary = $repository->generateSummary(
                $bankAccount->id,
                $startDate->toImmutable(),
                $endDate->toImmutable()
            );

            $reconciliationSummaries[] = [
                'bank_account_id' => $bankAccount->id,
                'bank_account_name' => $bankAccount->name,
                'summary' => [
                    'pending_items' => $summary->getPendingItems(),
                    'reconciled_items' => $summary->getReconciledItems(),
                    'discrepancy_items' => $summary->getDiscrepancyItems(),
                    'total_credits' => $summary->getTotalCredits()->getAmount(),
                    'total_debits' => $summary->getTotalDebits()->getAmount(),
                    'expected_balance' => $summary->getExpectedBalance()->getAmount(),
                    'actual_balance' => $summary->getActualBalance()->getAmount(),
                    'generated_at' => $summary->getGeneratedAt()->format('Y-m-d H:i:s'),
                ],
            ];
        }

        return Inertia::render('BankReconciliation/Index', [
            'bank_accounts' => $bankAccounts,
            'reconciliation_summaries' => $reconciliationSummaries,
        ]);
    }

    public function show(string $bankAccountId, ReconciliationRepositoryInterface $repository): Response
    {
        $bankAccount = \App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel::findOrFail($bankAccountId);

        $pendingItems = $repository->findPendingByBankAccountId($bankAccountId);

        $startDate = now()->subMonth()->startOfMonth();
        $endDate = now()->endOfMonth();

        $summary = $repository->generateSummary(
            $bankAccountId,
            $startDate->toImmutable(),
            $endDate->toImmutable()
        );

        return Inertia::render('BankReconciliation/Show', [
            'bank_account' => $bankAccount,
            'pending_items' => array_map(fn($item) => $item->toArray(), $pendingItems),
            'summary' => [
                'pending_items' => $summary->getPendingItems(),
                'reconciled_items' => $summary->getReconciledItems(),
                'discrepancy_items' => $summary->getDiscrepancyItems(),
                'total_credits' => $summary->getTotalCredits()->getAmount(),
                'total_debits' => $summary->getTotalDebits()->getAmount(),
                'expected_balance' => $summary->getExpectedBalance()->getAmount(),
                'actual_balance' => $summary->getActualBalance()->getAmount(),
                'generated_at' => $summary->getGeneratedAt()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function importForm(string $bankAccountId): Response
    {
        $bankAccount = \App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel::findOrFail($bankAccountId);

        return Inertia::render('BankReconciliation/Import', [
            'bank_account' => $bankAccount,
        ]);
    }

    public function import(
        ImportBankStatementRequest $request,
        ImportBankStatementHandler $handler,
        string $bankAccountId
    ): RedirectResponse {
        $validated = $request->validated();

        $items = [];
        foreach ($validated['items'] as $item) {
            $items[] = new \App\Application\BankReconciliation\DTOs\BankStatementItemData(
                id: $item['id'],
                date: \DateTimeImmutable::createFromFormat('Y-m-d', $item['date']),
                description: $item['description'],
                amount: $item['amount'],
                type: $item['type'],
                bankReference: $item['bank_reference'] ?? null,
                notes: $item['notes'] ?? null,
            );
        }

        $data = new ImportBankStatementData(
            bankAccountId: $bankAccountId,
            statementDate: \DateTimeImmutable::createFromFormat('Y-m-d', $validated['statement_date']),
            statementType: $validated['statement_type'],
            items: $items,
            originalFilename: $validated['original_filename'] ?? null,
            notes: $validated['notes'] ?? null,
        );

        $handler->handle($data);

        return redirect()->route('bank-reconciliation.show', $bankAccountId)
            ->with('success', 'Extrato bancário importado com sucesso.');
    }

    public function reconcileForm(string $bankAccountId): Response
    {
        $bankAccount = \App\Infrastructure\Persistence\Eloquent\Models\BankAccountModel::findOrFail($bankAccountId);

        $pendingItems = \App\Infrastructure\Persistence\Eloquent\Models\ReconciliationItemModel::query()
            ->where('bank_account_id', $bankAccountId)
            ->where('status', ReconciliationStatus::PENDING->value)
            ->orderBy('date')
            ->get();

        $transactions = \App\Infrastructure\Persistence\Eloquent\Models\TransactionModel::query()
            ->where('bank_account_id', $bankAccountId)
            ->where('status', 'pending')
            ->orderBy('payment_date')
            ->get();

        return Inertia::render('BankReconciliation/Reconcile', [
            'bank_account' => $bankAccount,
            'pending_items' => $pendingItems,
            'transactions' => $transactions,
        ]);
    }

    public function reconcile(
        ReconcileBankAccountRequest $request,
        ReconcileBankAccountHandler $handler,
        string $bankAccountId
    ): RedirectResponse {
        $validated = $request->validated();

        $items = [];
        foreach ($validated['items'] as $item) {
            $items[] = new \App\Application\BankReconciliation\DTOs\ReconciliationItemData(
                id: $item['id'],
                description: $item['description'],
                amount: $item['amount'],
                date: \DateTimeImmutable::createFromFormat('Y-m-d', $item['date']),
                status: ReconciliationStatus::from($item['status']),
                transactionId: $item['transaction_id'] ?? null,
                bankStatementId: $item['bank_statement_id'] ?? null,
                notes: $item['notes'] ?? null,
            );
        }

        $data = new ReconcileBankAccountData(
            bankAccountId: $bankAccountId,
            reconciliationDate: \DateTimeImmutable::createFromFormat('Y-m-d', $validated['reconciliation_date']),
            items: $items,
            notes: $validated['notes'] ?? null,
        );

        $handler->handle($data);

        return redirect()->route('bank-reconciliation.show', $bankAccountId)
            ->with('success', 'Conciliação realizada com sucesso.');
    }
}