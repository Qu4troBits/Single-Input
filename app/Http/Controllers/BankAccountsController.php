<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\BankAccounts\Data\CreateBankAccountData;
use App\Application\BankAccounts\Data\UpdateBankAccountData;
use App\Application\BankAccounts\Handlers\CreateBankAccountHandler;
use App\Application\BankAccounts\Handlers\DeleteBankAccountHandler;
use App\Application\BankAccounts\Handlers\UpdateBankAccountHandler;
use App\Domain\BankAccounts\BankAccountId;
use App\Domain\BankAccounts\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\BankAccountStatus;
use App\Domain\BankAccounts\BankAccountType;
use App\Domain\Shared\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountsController extends Controller
{
    public function index(BankAccountRepositoryInterface $repository): Response
    {
        $bankAccounts = $repository->findAll();

        return Inertia::render('BankAccounts/Index', [
            'bankAccounts' => array_map(fn ($bankAccount) => [
                'id' => $bankAccount->getId()->toString(),
                'name' => $bankAccount->getName(),
                'type' => $bankAccount->getType()->value,
                'status' => $bankAccount->getStatus()->value,
                'bank_code' => $bankAccount->getBankCode(),
                'agency' => $bankAccount->getAgency(),
                'account_number' => $bankAccount->getAccountNumber(),
                'account_digit' => $bankAccount->getAccountDigit(),
                'description' => $bankAccount->getDescription(),
                'balance' => $bankAccount->getBalance()->toNumeric(),
                'initial_balance' => $bankAccount->getInitialBalance()->toNumeric(),
                'created_at' => $bankAccount->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $bankAccount->getUpdatedAt()->format('Y-m-d H:i:s'),
            ], $bankAccounts),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BankAccounts/Create', [
            'types' => array_map(fn ($type) => $type->value, BankAccountType::cases()),
        ]);
    }

    public function store(Request $request, CreateBankAccountHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_map(fn ($type) => $type->value, BankAccountType::cases())),
            'bank_code' => 'nullable|string|max:10',
            'agency' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:30',
            'account_digit' => 'nullable|string|max:2',
            'description' => 'nullable|string',
            'initial_balance' => 'required|numeric',
        ]);

        $bankAccountId = $handler->handle(new CreateBankAccountData(
            name: $validated['name'],
            type: BankAccountType::from($validated['type']),
            bankCode: $validated['bank_code'] ?? null,
            agency: $validated['agency'] ?? null,
            accountNumber: $validated['account_number'] ?? null,
            accountDigit: $validated['account_digit'] ?? null,
            description: $validated['description'] ?? null,
            initialBalance: Money::of($validated['initial_balance']),
        ));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária criada com sucesso.');
    }

    public function edit(string $id, BankAccountRepositoryInterface $repository): Response
    {
        $bankAccount = $repository->findById(BankAccountId::fromString($id));

        if ($bankAccount === null) {
            abort(404);
        }

        return Inertia::render('BankAccounts/Edit', [
            'bankAccount' => [
                'id' => $bankAccount->getId()->toString(),
                'name' => $bankAccount->getName(),
                'type' => $bankAccount->getType()->value,
                'status' => $bankAccount->getStatus()->value,
                'bank_code' => $bankAccount->getBankCode(),
                'agency' => $bankAccount->getAgency(),
                'account_number' => $bankAccount->getAccountNumber(),
                'account_digit' => $bankAccount->getAccountDigit(),
                'description' => $bankAccount->getDescription(),
            ],
            'types' => array_map(fn ($type) => $type->value, BankAccountType::cases()),
            'statuses' => array_map(fn ($status) => $status->value, BankAccountStatus::cases()),
        ]);
    }

    public function update(Request $request, string $id, UpdateBankAccountHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_map(fn ($type) => $type->value, BankAccountType::cases())),
            'status' => 'required|in:' . implode(',', array_map(fn ($status) => $status->value, BankAccountStatus::cases())),
            'bank_code' => 'nullable|string|max:10',
            'agency' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:30',
            'account_digit' => 'nullable|string|max:2',
            'description' => 'nullable|string',
        ]);

        $handler->handle(
            BankAccountId::fromString($id),
            new UpdateBankAccountData(
                name: $validated['name'],
                type: BankAccountType::from($validated['type']),
                status: BankAccountStatus::from($validated['status']),
                bankCode: $validated['bank_code'] ?? null,
                agency: $validated['agency'] ?? null,
                accountNumber: $validated['account_number'] ?? null,
                accountDigit: $validated['account_digit'] ?? null,
                description: $validated['description'] ?? null,
            )
        );

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária atualizada com sucesso.');
    }

    public function destroy(string $id, DeleteBankAccountHandler $handler): RedirectResponse
    {
        $handler->handle(BankAccountId::fromString($id));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária excluída com sucesso.');
    }
}