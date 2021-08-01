<?php

namespace Jawabapp\Community\Http\Requests\TagGroup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'parent_id' => 'nullable|integer',
            'order' => 'required|integer',
            'name' => 'required|array',
            'name.*' => 'required|string',
            'image_file' => 'nullable|image',
        ];
    }
}
