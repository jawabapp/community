<?php

namespace Jawabapp\Community\Http\Requests\Account\Block;

use Illuminate\Foundation\Http\FormRequest;

class UnblockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'block_account_id' => 'required|integer'
        ];
    }
}
