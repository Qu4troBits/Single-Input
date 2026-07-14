<?php

declare(strict_types=1);

namespace App\Http\Requests\BankReconciliation;

use Illuminate\Foundation\Http\FormRequest;

final class ReconcileBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reconcile-bank-account');
    }

    public function rules(): array
    {
        return [
            'reconciliation_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric'],
            'items.*.date' => ['required', 'date'],
            'items.*.status' => ['required', 'in:pending,reconciled,discrepancy,adjusted'],
            'items.*.transaction_id' => ['nullable', 'string', 'max:36'],
            'items.*.bank_statement_id' => ['nullable', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reconciliation_date.required' => 'A data da conciliação é obrigatória.',
            'reconciliation_date.before_or_equal' => 'A data da conciliação não pode ser futura.',
            'items.required' => 'Pelo menos um item de conciliação é necessário.',
            'items.*.id.required' => 'O ID do item é obrigatório.',
            'items.*.description.required' => 'A descrição do item é obrigatória.',
            'items.*.amount.required' => 'O valor do item é obrigatório.',
            'items.*.amount.numeric' => 'O valor deve ser numérico.',
            'items.*.date.required' => 'A data do item é obrigatória.',
            'items.*.status.required' => 'O status do item é obrigatório.',
            'items.*.status.in' => 'Status inválido.',
        ];
    }
}