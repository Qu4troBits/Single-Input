<?php

declare(strict_types=1);

namespace App\Http\Requests\BankAccount;

use App\Domain\BankAccounts\ValueObjects\BankAccountType;
use Illuminate\Foundation\Http\FormRequest;

final class CreateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', BankAccountType::values()),
            'bank_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:255',
            'agency_number' => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_digit' => 'nullable|string|max:2',
            'initial_balance' => 'required|numeric|min:-9999999999.99|max:9999999999.99',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'include_in_dashboard' => 'boolean',
            'include_in_reports' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da conta é obrigatório.',
            'type.required' => 'O tipo da conta é obrigatório.',
            'type.in' => 'Tipo de conta inválido.',
            'bank_code.required' => 'O código do banco é obrigatório.',
            'bank_name.required' => 'O nome do banco é obrigatório.',
            'agency_number.required' => 'O número da agência é obrigatório.',
            'account_number.required' => 'O número da conta é obrigatório.',
            'initial_balance.required' => 'O saldo inicial é obrigatório.',
            'initial_balance.numeric' => 'O saldo inicial deve ser um valor numérico.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
        ];
    }
}
