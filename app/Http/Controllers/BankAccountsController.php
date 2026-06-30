<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\BankAccounts\DTOs\CreateBankAccountData;
use App\Application\BankAccounts\DTOs\UpdateBankAccountData;
use App\Application\BankAccounts\Handlers\CreateBankAccountHandler;
use App\Application\BankAccounts\Handlers\DeleteBankAccountHandler;
use App\Application\BankAccounts\Handlers\UpdateBankAccountHandler;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
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
                'bankCode' => $bankAccount->getBankCode(),
                'bankName' => $bankAccount->getBankName(),
                'agencyNumber' => $bankAccount->getAgencyNumber(),
                'accountNumber' => $bankAccount->getAccountNumber(),
                'accountDigit' => $bankAccount->getAccountDigit(),
                'initialBalance' => $bankAccount->getInitialBalance()->toNumeric(),
                'currentBalance' => $bankAccount->getCurrentBalance()->toNumeric(),
                'status' => $bankAccount->getStatus()->value,
                'description' => $bankAccount->getDescription(),
                'color' => $bankAccount->getColor(),
                'icon' => $bankAccount->getIcon(),
                'includeInDashboard' => $bankAccount->isIncludeInDashboard(),
                'includeInReports' => $bankAccount->isIncludeInReports(),
                'isDefault' => $bankAccount->isDefault(),
                'createdAt' => $bankAccount->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $bankAccount->getUpdatedAt()->format('Y-m-d H:i:s'),
            ], $bankAccounts),
            'meta' => [
                'total' => count($bankAccounts),
                'per_page' => 15,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => count($bankAccounts),
            ],
            'filters' => [],
            'bankAccountTypes' => array_map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], BankAccountType::cases()),
            'bankAccountStatuses' => array_map(fn ($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], BankAccountStatus::cases()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BankAccounts/Create', [
            'bankAccountTypes' => array_map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], BankAccountType::cases()),
        ]);
    }

    public function store(Request $request, CreateBankAccountHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_map(fn ($type) => $type->value, BankAccountType::cases())),
            'bank_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:255',
            'agency_number' => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_digit' => 'nullable|string|max:2',
            'initial_balance' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'include_in_dashboard' => 'boolean',
            'include_in_reports' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $type = BankAccountType::tryFrom($validated['type']);
        if ($type === null) {
            throw new \InvalidArgumentException('Invalid bank account type.');
        }
        
        $bankAccountId = $handler->handle(new CreateBankAccountData(
            name: $validated['name'],
            type: $type,
            bankCode: $validated['bank_code'],
            bankName: $validated['bank_name'],
            agencyNumber: $validated['agency_number'],
            accountNumber: $validated['account_number'],
            accountDigit: $validated['account_digit'] ?? null,
            initialBalance: Money::of($validated['initial_balance']),
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            icon: $validated['icon'] ?? null,
            includeInDashboard: $validated['include_in_dashboard'] ?? true,
            includeInReports: $validated['include_in_reports'] ?? true,
            isDefault: $validated['is_default'] ?? false,
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
                'bankCode' => $bankAccount->getBankCode(),
                'bankName' => $bankAccount->getBankName(),
                'agencyNumber' => $bankAccount->getAgencyNumber(),
                'accountNumber' => $bankAccount->getAccountNumber(),
                'accountDigit' => $bankAccount->getAccountDigit(),
                'initialBalance' => $bankAccount->getInitialBalance()->toNumeric(),
                'currentBalance' => $bankAccount->getCurrentBalance()->toNumeric(),
                'status' => $bankAccount->getStatus()->value,
                'description' => $bankAccount->getDescription(),
                'color' => $bankAccount->getColor(),
                'icon' => $bankAccount->getIcon(),
                'includeInDashboard' => $bankAccount->isIncludeInDashboard(),
                'includeInReports' => $bankAccount->isIncludeInReports(),
                'isDefault' => $bankAccount->isDefault(),
                'createdAt' => $bankAccount->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $bankAccount->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            'bankAccountTypes' => array_map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], BankAccountType::cases()),
            'bankAccountStatuses' => array_map(fn ($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], BankAccountStatus::cases()),
        ]);
    }

    public function update(Request $request, string $id, UpdateBankAccountHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_map(fn ($type) => $type->value, BankAccountType::cases())),
            'bank_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:255',
            'agency_number' => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_digit' => 'nullable|string|max:2',
            'initial_balance' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'include_in_dashboard' => 'boolean',
            'include_in_reports' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $type = BankAccountType::tryFrom($validated['type']);
        if ($type === null) {
            throw new \InvalidArgumentException('Invalid bank account type.');
        }
        
        $handler->handle(new UpdateBankAccountData(
            id: BankAccountId::fromString($id),
            name: $validated['name'],
            type: $type,
            bankCode: $validated['bank_code'],
            bankName: $validated['bank_name'],
            agencyNumber: $validated['agency_number'],
            accountNumber: $validated['account_number'],
            accountDigit: $validated['account_digit'] ?? null,
            initialBalance: Money::of($validated['initial_balance']),
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            icon: $validated['icon'] ?? null,
            includeInDashboard: $validated['include_in_dashboard'] ?? true,
            includeInReports: $validated['include_in_reports'] ?? true,
            isDefault: $validated['is_default'] ?? false,
        ));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária atualizada com sucesso.');
    }

    public function destroy(string $id, DeleteBankAccountHandler $handler): RedirectResponse
    {
        $handler->handle(BankAccountId::fromString($id));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária excluída com sucesso.');
    }

    public function activate(string $id, DeleteBankAccountHandler $handler): RedirectResponse
    {
        $handler->activate(BankAccountId::fromString($id));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária ativada com sucesso.');
    }

    public function deactivate(string $id, DeleteBankAccountHandler $handler): RedirectResponse
    {
        $handler->deactivate(BankAccountId::fromString($id));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária desativada com sucesso.');
    }
}