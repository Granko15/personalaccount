<?php

namespace App\Http\Requests\FinancialOperations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class CreateOperationRequest extends FormRequest
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
            'account_id' => ['required', 'numeric', 'exists:accounts,id'],
            'title' => ['required', "max:255"],
            'date' => ['required', 'date'],
            'operation_type_id' => ['required', "max:255"],
            'subject' => ['required', "max:255"],
            'sum' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', File::types(['txt','pdf'])],
            'expected_date_of_return' => ['nullable', 'date'],
            'previous_lending_id' => ['nullable', 'numeric', 'exists:lendings,id'],
        ];
    }
}
