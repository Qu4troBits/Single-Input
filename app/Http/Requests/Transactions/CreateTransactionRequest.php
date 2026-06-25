<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'string', 'regex:/^-?\\d+(?:\\.\\d{1,2})?$/'],
            'direction' => ['required', 'string', Rule::in(['in', 'out'])],
            'status' => ['required', 'string', Rule::in(['pending', 'paid', 'cancelled'])],
            'competence_month' => ['required', 'date_format:Y-m-d'],
            'payment_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
