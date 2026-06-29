<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateDreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $periodType = $this->input('period_type', 'monthly');

        $rules = [
            'period_type' => 'required|string|in:monthly,quarterly,yearly,custom',
            'category_id' => 'nullable|string|exists:categories,id',
            'scenario' => 'string|in:base,optimistic,pessimistic,conservative',
        ];

        switch ($periodType) {
            case 'monthly':
                $rules['year_month'] = 'required|date_format:Y-m';
                break;
                
            case 'quarterly':
                $rules['year'] = 'required|date_format:Y';
                $rules['quarter'] = 'required|integer|min:1|max:4';
                break;
                
            case 'yearly':
                $rules['year'] = 'required|date_format:Y';
                break;
                
            case 'custom':
                $rules['start_date'] = 'required|date';
                $rules['end_date'] = 'required|date|after_or_equal:start_date';
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'period_type.required' => 'O tipo de período é obrigatório.',
            'period_type.in' => 'O tipo de período deve ser: mensal, trimestral, anual ou customizado.',
            
            'year_month.required' => 'O mês/ano é obrigatório para período mensal.',
            'year_month.date_format' => 'O mês/ano deve estar no formato YYYY-MM.',
            
            'year.required' => 'O ano é obrigatório.',
            'year.date_format' => 'O ano deve estar no formato YYYY.',
            
            'quarter.required' => 'O trimestre é obrigatório.',
            'quarter.integer' => 'O trimestre deve ser um número inteiro.',
            'quarter.min' => 'O trimestre deve ser no mínimo 1.',
            'quarter.max' => 'O trimestre deve ser no máximo 4.',
            
            'start_date.required' => 'A data inicial é obrigatória para período customizado.',
            'start_date.date' => 'A data inicial deve ser uma data válida.',
            
            'end_date.required' => 'A data final é obrigatória para período customizado.',
            'end_date.date' => 'A data final deve ser uma data válida.',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            
            'category_id.exists' => 'A categoria selecionada não existe.',
            
            'scenario.in' => 'O cenário deve ser: base, otimista, pessimista ou conservador.',
        ];
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);
        
        // Garantir valores padrão
        $validated['scenario'] = $validated['scenario'] ?? 'base';
        $validated['quarter'] = isset($validated['quarter']) ? (int) $validated['quarter'] : 0;
        
        return $validated;
    }
}