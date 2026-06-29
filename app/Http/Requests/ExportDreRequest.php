<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExportDreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => 'required|string|in:pdf,excel,csv,json',
            'include_details' => 'boolean',
            'include_ratios' => 'boolean',
            'include_charts' => 'boolean',
            'language' => 'string|in:pt-BR,en-US,es-ES',
            'currency' => 'string|in:BRL,USD,EUR,GBP',
        ];
    }

    public function messages(): array
    {
        return [
            'format.required' => 'O formato de exportação é obrigatório.',
            'format.in' => 'O formato deve ser: PDF, Excel, CSV ou JSON.',
            
            'include_details.boolean' => 'Incluir detalhes deve ser verdadeiro ou falso.',
            'include_ratios.boolean' => 'Incluir índices deve ser verdadeiro ou falso.',
            'include_charts.boolean' => 'Incluir gráficos deve ser verdadeiro ou falso.',
            
            'language.in' => 'O idioma deve ser: Português (Brasil), Inglês (EUA) ou Espanhol (Espanha).',
            'currency.in' => 'A moeda deve ser: Real Brasileiro, Dólar Americano, Euro ou Libra Esterlina.',
        ];
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);
        
        // Garantir valores padrão
        $validated['include_details'] = $validated['include_details'] ?? true;
        $validated['include_ratios'] = $validated['include_ratios'] ?? true;
        $validated['include_charts'] = $validated['include_charts'] ?? false;
        $validated['language'] = $validated['language'] ?? 'pt-BR';
        $validated['currency'] = $validated['currency'] ?? 'BRL';
        
        return $validated;
    }
}