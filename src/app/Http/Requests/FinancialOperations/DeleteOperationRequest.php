<?php

namespace App\Http\Requests\FinancialOperations;

use Illuminate\Foundation\Http\FormRequest;

class DeleteOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'operation_id' => ['required', 'numeric', 'exists:financial_operations,id']
        ];
    }
}
