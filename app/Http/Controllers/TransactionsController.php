<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Banking\ListBankAccounts\ListBankAccountsHandler;
use App\Application\Categories\ListCategories\ListCategoriesHandler;
use App\Application\Transactions\CreateTransaction\CreateTransactionData;
use App\Application\Transactions\CreateTransaction\CreateTransactionHandler;
use App\Application\Transactions\ListTransactions\ListTransactionsHandler;
use App\Http\Requests\Transactions\CreateTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class TransactionsController extends Controller
{
    public function index(ListTransactionsHandler $handler): Response
    {
        $transactions = $handler->handle();

        return Inertia::render('Transactions/Index', [
            'transactions' => array_map(static function ($t): array {
                return [
                    'id' => $t->id,
                    'description' => $t->description,
                    'amount' => $t->amount->toString(),
                    'direction' => $t->direction->value,
                    'status' => $t->status->value,
                    'competence_month' => $t->competenceMonth,
                    'payment_date' => $t->paymentDate,
                ];
            }, $transactions),
        ]);
    }

    public function create(ListBankAccountsHandler $bankAccounts, ListCategoriesHandler $categories): Response
    {
        $accounts = $bankAccounts->handle();
        $cats = $categories->handle();

        return Inertia::render('Transactions/Create', [
            'bankAccounts' => array_map(static fn ($a): array => ['id' => $a->id, 'name' => $a->name], $accounts),
            'categories' => array_map(static fn ($c): array => ['id' => $c->id, 'name' => $c->name], $cats),
        ]);
    }

    public function store(CreateTransactionRequest $request, CreateTransactionHandler $handler): RedirectResponse
    {
        $handler->handle(new CreateTransactionData(
            bankAccountId: (int) $request->integer('bank_account_id'),
            categoryId: (int) $request->integer('category_id'),
            description: (string) $request->string('description'),
            amount: (string) $request->string('amount'),
            direction: (string) $request->string('direction'),
            status: (string) $request->string('status'),
            competenceMonth: (string) $request->string('competence_month'),
            paymentDate: $request->filled('payment_date') ? (string) $request->string('payment_date') : null,
        ));

        return redirect()->route('transactions.index');
    }
}
