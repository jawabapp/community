<?php

namespace JawabApp\Community\Http\Requests\Community\Post;

use JawabApp\Community\Models\PostInteraction;
use Illuminate\Foundation\Http\FormRequest;

class InteractionRequest extends FormRequest
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
            'account_id' => 'required|integer',
            'type' => 'required|string|in:' . implode(',', PostInteraction::TYPES),
            'isRemove' => 'nullable|boolean'
        ];
    }
}
