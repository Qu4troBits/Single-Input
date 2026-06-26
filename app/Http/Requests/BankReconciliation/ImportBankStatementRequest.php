<?php

declare(strict_types=1);

namespace App\Http\Requests\BankReconciliation;

use Illuminate\Foundation\Http\FormRequest;

final class ImportBankStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('import-bank-statement');
    }

    public function rules(): array
    {
        return [
            'statement_date' => ['required', 'date', 'before_or_equal:today'],
            'statement_type' => ['required', 'in:csv,ofx,pdf,manual'],
            'original_filename' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'string', 'max:100'],
            'items.*.date' => ['required', 'date'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.type' => ['required', 'in:credit,debit'],
            'items.*.bank_reference' => ['nullable', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'statement_date.required' => 'A data do extrato é obrigatória.',
            'statement_date.before_or_equal' => 'A data do extrato não pode ser futura.',
            'statement_type.required' => 'O tipo de extrato é obrigatório.',
            'statement_type.in' => 'Tipo de extrato inválido.',
            'items.required' => 'Pelo menos um item do extrato é necessário.',
            'items.*.id.required' => 'O ID do item é obrigatório.',
            'items.*.date.required' => 'A data do item é obrigatória.',
            'items.*.description.required' => 'A descrição do item é obrigatória.',
            'items.*.amount.required' => 'O valor do item é obrigatório.',
            'items.*.amount.numeric' => 'O valor deve ser numérico.',
            'items.*.type.required' => 'O tipo de transação é obrigatório.',
            'items.*.type.in' => 'Tipo de transação inválido.',
        ];
    }
}