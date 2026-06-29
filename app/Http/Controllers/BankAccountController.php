<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\BankAccounts\DTOs\CreateBankAccountData;
use App\Application\BankAccounts\DTOs\UpdateBankAccountData;
use App\Application\BankAccounts\Handlers\CreateBankAccountHandler;
use App\Application\BankAccounts\Handlers\DeleteBankAccountHandler;
use App\Application\BankAccounts\Handlers\UpdateBankAccountHandler;
use App\Domain\BankAccounts\Repositories\BankAccountRepositoryInterface;
use App\Domain\BankAccounts\ValueObjects\BankAccountId;
use App\Domain\BankAccounts\ValueObjects\BankAccountStatus;
use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use App\Domain\Shared\Money;
use App\Http\Requests\BankAccount\CreateBankAccountRequest;
use App\Http\Requests\BankAccount\UpdateBankAccountRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountRepositoryInterface $bankAccountRepository,
        private readonly CreateBankAccountHandler $createBankAccountHandler,
        private readonly UpdateBankAccountHandler $updateBankAccountHandler,
        private readonly DeleteBankAccountHandler $deleteBankAccountHandler,
    ) {}

    public function index(Request $request): Response
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $includeInDashboard = $request->boolean('include_in_dashboard', false);
        $includeInReports = $request->boolean('include_in_reports', false);
        $isDefault = $request->boolean('is_default', false);
        $page = (int) $request->query('page', 1);

        $bankAccountType = $type ? BankAccountType::from($type) : null;
        $bankAccountStatus = $status ? BankAccountStatus::from($status) : null;

        $result = $this->bankAccountRepository->findAll(
            type: $bankAccountType,
            status: $bankAccountStatus,
            includeInDashboard: $includeInDashboard,
            includeInReports: $includeInReports,
            isDefault: $isDefault,
            page: $page,
            perPage: 20
        );

        return Inertia::render('BankAccounts/Index', [
            'bankAccounts' => $result['data'],
            'meta' => $result['meta'],
            'filters' => [
                'type' => $type,
                'status' => $status,
                'include_in_dashboard' => $includeInDashboard,
                'include_in_reports' => $includeInReports,
                'is_default' => $isDefault,
            ],
            'bankAccountTypes' => array_map(
                fn (BankAccountType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                BankAccountType::cases()
            ),
            'bankAccountStatuses' => array_map(
                fn (BankAccountStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ],
                BankAccountStatus::cases()
            ),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BankAccounts/Create', [
            'bankAccountTypes' => array_map(
                fn (BankAccountType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                BankAccountType::cases()
            ),
        ]);
    }

    public function store(CreateBankAccountRequest $request): Response
    {
        $data = new CreateBankAccountData(
            name: $request->validated('name'),
            type: BankAccountType::from($request->validated('type')),
            bankCode: $request->validated('bank_code'),
            bankName: $request->validated('bank_name'),
            agencyNumber: $request->validated('agency_number'),
            accountNumber: $request->validated('account_number'),
            accountDigit: $request->validated('account_digit'),
            initialBalance: Money::of($request->validated('initial_balance', '0')),
            description: $request->validated('description'),
            color: $request->validated('color'),
            icon: $request->validated('icon'),
            includeInDashboard: $request->boolean('include_in_dashboard', true),
            includeInReports: $request->boolean('include_in_reports', true),
            isDefault: $request->boolean('is_default', false),
        );

        $bankAccount = $this->createBankAccountHandler->handle($data);

        return redirect()
            ->route('bank-accounts.show', $bankAccount->getId()->toString())
            ->with('success', 'Conta bancária criada com sucesso.');
    }

    public function show(string $id): Response
    {
        $bankAccount = $this->bankAccountRepository->findById(
            BankAccountId::fromString($id)
        );

        if (!$bankAccount) {
            abort(404, 'Conta bancária não encontrada.');
        }

        return Inertia::render('BankAccounts/Show', [
            'bankAccount' => $bankAccount,
        ]);
    }

    public function edit(string $id): Response
    {
        $bankAccount = $this->bankAccountRepository->findById(
            BankAccountId::fromString($id)
        );

        if (!$bankAccount) {
            abort(404, 'Conta bancária não encontrada.');
        }

        return Inertia::render('BankAccounts/Edit', [
            'bankAccount' => $bankAccount,
            'bankAccountTypes' => array_map(
                fn (BankAccountType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                BankAccountType::cases()
            ),
            'bankAccountStatuses' => array_map(
                fn (BankAccountStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ],
                BankAccountStatus::cases()
            ),
        ]);
    }

    public function update(UpdateBankAccountRequest $request, string $id): Response
    {
        $data = new UpdateBankAccountData(
            id: BankAccountId::fromString($id),
            name: $request->validated('name'),
            type: BankAccountType::from($request->validated('type')),
            bankCode: $request->validated('bank_code'),
            bankName: $request->validated('bank_name'),
            agencyNumber: $request->validated('agency_number'),
            accountNumber: $request->validated('account_number'),
            accountDigit: $request->validated('account_digit'),
            initialBalance: Money::of($request->validated('initial_balance', '0')),
            description: $request->validated('description'),
            color: $request->validated('color'),
            icon: $request->validated('icon'),
            includeInDashboard: $request->boolean('include_in_dashboard', true),
            includeInReports: $request->boolean('include_in_reports', true),
            isDefault: $request->boolean('is_default', false),
        );

        $bankAccount = $this->updateBankAccountHandler->handle($data);

        return redirect()
            ->route('bank-accounts.show', $bankAccount->getId()->toString())
            ->with('success', 'Conta bancária atualizada com sucesso.');
    }

    public function destroy(string $id): Response
    {
        $this->deleteBankAccountHandler->handle(
            BankAccountId::fromString($id)
        );

        return redirect()
            ->route('bank-accounts.index')
            ->with('success', 'Conta bancária excluída com sucesso.');
    }
}
