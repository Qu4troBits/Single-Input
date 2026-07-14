<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\Domain\Categories\ValueObjects\CategoryType;
use Illuminate\Foundation\Http\FormRequest;

final class CreateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', CategoryType::values()),
            'code' => 'required|string|max:20|regex:/^[A-Z0-9_]+$/',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'is_operating' => 'boolean',
            'is_tax_deductible' => 'boolean',
            'include_in_reports' => 'boolean',
            'is_default' => 'boolean',
            'parent_id' => 'nullable|string|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'type.required' => 'O tipo da categoria é obrigatório.',
            'type.in' => 'Tipo de categoria inválido.',
            'code.required' => 'O código da categoria é obrigatório.',
            'code.regex' => 'O código deve conter apenas letras maiúsculas, números e underscores.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
            'parent_id.exists' => 'A categoria pai selecionada não existe.',
        ];
    }
}
