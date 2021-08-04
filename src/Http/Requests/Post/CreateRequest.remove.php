<?php

namespace Jawabapp\Community\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequestRemove extends FormRequest
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
            'name'    => 'required|string',
            'slug'    => "required|unique:static_pages,slug,{$this->id},id,language_code,{$this->language_code}",
            'language_code'  => 'required|string|max:2',
            'html'  => 'required|string',
        ];
    }
}
