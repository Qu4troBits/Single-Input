<?php

declare(strict_types=1);

namespace App\Http\Requests\FinancialProjections;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateProjectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('generate-financial-projection');
    }

    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'in:revenue,expense,profit,cash_flow,balance_sheet'],
            'period_type' => ['required', 'in:monthly,quarterly,yearly'],
            'scenario' => ['required', 'in:base,optimistic,pessimistic,custom'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'string', 'max:36'],
        ];

        // Regras condicionais baseadas no tipo de período
        if ($this->input('period_type') === 'monthly') {
            $rules['year_month'] = ['required', 'regex:/^\d{4}-\d{2}$/'];
        }

        if ($this->input('period_type') === 'quarterly') {
            $rules['year'] = ['required', 'digits:4'];
            $rules['quarter'] = ['required', 'integer', 'min:1', 'max:4'];
        }

        if ($this->input('period_type') === 'yearly') {
            $rules['year'] = ['required', 'digits:4'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O tipo de projeção é obrigatório.',
            'type.in' => 'Tipo de projeção inválido.',
            'period_type.required' => 'O tipo de período é obrigatório.',
            'period_type.in' => 'Tipo de período inválido.',
            'scenario.required' => 'O cenário é obrigatório.',
            'scenario.in' => 'Cenário inválido.',
            'year_month.required' => 'O mês/ano é obrigatório para projeções mensais.',
            'year_month.regex' => 'Formato inválido para mês/ano. Use YYYY-MM.',
            'year.required' => 'O ano é obrigatório.',
            'year.digits' => 'O ano deve ter 4 dígitos.',
            'quarter.required' => 'O trimestre é obrigatório.',
            'quarter.integer' => 'O trimestre deve ser um número inteiro.',
            'quarter.min' => 'O trimestre deve ser no mínimo 1.',
            'quarter.max' => 'O trimestre deve ser no máximo 4.',
        ];
    }
}