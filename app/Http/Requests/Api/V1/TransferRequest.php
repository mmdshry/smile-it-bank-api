<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'source_account_id' => [
                'required',
                Rule::exists('accounts', 'id')
            ],
            'destination_account_id' => [
                'required',
                Rule::exists('accounts', 'id'),
                'different:source_account_id'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:1'
            ]
        ];
    }
}
