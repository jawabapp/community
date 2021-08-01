<?php

namespace Jawabapp\Community\Http\Requests\Community\Tag\Follow;

use Illuminate\Foundation\Http\FormRequest;

class UnFollowRequest extends FormRequest
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
            'hash_tag' => 'required_without:hash_tag_id|string',
            'hash_tag_id' => 'required_without:hash_tag|integer',
        ];
    }
}
