<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Transactions\Data\CreateTransactionData;
use App\Application\Transactions\Data\UpdateTransactionData;
use App\Application\Transactions\Handlers\CreateTransactionHandler;
use App\Application\Transactions\Handlers\DeleteTransactionHandler;
use App\Application\Transactions\Handlers\MarkTransactionAsCancelledHandler;
use App\Application\Transactions\Handlers\MarkTransactionAsPaidHandler;
use App\Application\Transactions\Handlers\UpdateTransactionHandler;
use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountRepositoryInterface;
use App\Domain\Categories\CategoryId;
use App\Domain\Categories\CategoryRepositoryInterface;
use App\Domain\Shared\Money;
use App\Domain\Transactions\TransactionDirection;
use App\Domain\Transactions\TransactionId;
use App\Domain\Transactions\TransactionRepositoryInterface;
use App\Domain\Transactions\TransactionStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TransactionsController extends Controller
{
    public function index(TransactionRepositoryInterface $repository): Response
    {
        $transactions = $repository->findAll();

        return Inertia::render('Transactions/Index', [
            'transactions' => array_map(fn ($transaction) => [
                'id' => $transaction->getId()->toString(),
                'bank_account_id' => $transaction->getBankAccountId()->toString(),
                'category_id' => $transaction->getCategoryId()->toString(),
                'description' => $transaction->getDescription(),
                'amount' => $transaction->getAmount()->toNumeric(),
                'direction' => $transaction->getDirection()->value,
                'status' => $transaction->getStatus()->value,
                'competence_month' => $transaction->getCompetenceMonth(),
                'payment_date' => $transaction->getPaymentDate()?->format('Y-m-d'),
                'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $transaction->getUpdatedAt()->format('Y-m-d H:i:s'),
            ], $transactions),
        ]);
    }

    public function create(
        BankAccountRepositoryInterface $bankAccountRepository,
        CategoryRepositoryInterface $categoryRepository
    ): Response {
        $bankAccounts = $bankAccountRepository->findAll();
        $categories = $categoryRepository->findAll();

        return Inertia::render('Transactions/Create', [
            'bankAccounts' => array_map(fn ($account) => [
                'id' => $account->getId()->toString(),
                'name' => $account->getName(),
            ], $bankAccounts),
            'categories' => array_map(fn ($category) => [
                'id' => $category->getId()->toString(),
                'name' => $category->getName(),
                'type' => $category->getType()->value,
            ], $categories),
            'directions' => array_map(fn ($direction) => $direction->value, TransactionDirection::cases()),
        ]);
    }

    public function store(Request $request, CreateTransactionHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|uuid',
            'category_id' => 'required|uuid',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'direction' => 'required|in:' . implode(',', array_map(fn ($direction) => $direction->value, TransactionDirection::cases())),
            'competence_month' => 'required|date_format:Y-m',
            'payment_date' => 'nullable|date',
        ]);

        $transactionId = $handler->handle(new CreateTransactionData(
            bankAccountId: BankAccountId::fromString($validated['bank_account_id']),
            categoryId: CategoryId::fromString($validated['category_id']),
            description: $validated['description'],
            amount: Money::of($validated['amount']),
            direction: TransactionDirection::from($validated['direction']),
            competenceMonth: $validated['competence_month'],
            paymentDate: isset($validated['payment_date']) ? new \DateTimeImmutable($validated['payment_date']) : null,
        ));

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento criado com sucesso.');
    }

    public function edit(
        string $id,
        TransactionRepositoryInterface $transactionRepository,
        BankAccountRepositoryInterface $bankAccountRepository,
        CategoryRepositoryInterface $categoryRepository
    ): Response {
        $transaction = $transactionRepository->findById(TransactionId::fromString($id));

        if ($transaction === null) {
            abort(404);
        }

        $bankAccounts = $bankAccountRepository->findAll();
        $categories = $categoryRepository->findAll();

        return Inertia::render('Transactions/Edit', [
            'transaction' => [
                'id' => $transaction->getId()->toString(),
                'bank_account_id' => $transaction->getBankAccountId()->toString(),
                'category_id' => $transaction->getCategoryId()->toString(),
                'description' => $transaction->getDescription(),
                'amount' => $transaction->getAmount()->toNumeric(),
                'direction' => $transaction->getDirection()->value,
                'status' => $transaction->getStatus()->value,
                'competence_month' => $transaction->getCompetenceMonth(),
                'payment_date' => $transaction->getPaymentDate()?->format('Y-m-d'),
            ],
            'bankAccounts' => array_map(fn ($account) => [
                'id' => $account->getId()->toString(),
                'name' => $account->getName(),
            ], $bankAccounts),
            'categories' => array_map(fn ($category) => [
                'id' => $category->getId()->toString(),
                'name' => $category->getName(),
                'type' => $category->getType()->value,
            ], $categories),
            'directions' => array_map(fn ($direction) => $direction->value, TransactionDirection::cases()),
            'statuses' => array_map(fn ($status) => $status->value, TransactionStatus::cases()),
        ]);
    }

    public function update(Request $request, string $id, UpdateTransactionHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|uuid',
            'category_id' => 'required|uuid',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'direction' => 'required|in:' . implode(',', array_map(fn ($direction) => $direction->value, TransactionDirection::cases())),
            'competence_month' => 'required|date_format:Y-m',
        ]);

        $handler->handle(
            TransactionId::fromString($id),
            new UpdateTransactionData(
                bankAccountId: BankAccountId::fromString($validated['bank_account_id']),
                categoryId: CategoryId::fromString($validated['category_id']),
                description: $validated['description'],
                amount: Money::of($validated['amount']),
                direction: TransactionDirection::from($validated['direction']),
                competenceMonth: $validated['competence_month'],
            )
        );

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function destroy(string $id, DeleteTransactionHandler $handler): RedirectResponse
    {
        $handler->handle(TransactionId::fromString($id));

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento excluído com sucesso.');
    }

    public function markAsPaid(string $id, MarkTransactionAsPaidHandler $handler): RedirectResponse
    {
        $handler->handle(
            TransactionId::fromString($id),
            new \DateTimeImmutable()
        );

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento marcado como pago.');
    }

    public function markAsCancelled(string $id, MarkTransactionAsCancelledHandler $handler): RedirectResponse
    {
        $handler->handle(TransactionId::fromString($id));

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento cancelado.');
    }
}