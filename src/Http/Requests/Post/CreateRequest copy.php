<?php

namespace Jawabapp\Community\Http\Requests\Community\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
        $rules = [
            'account_id' => 'required|integer',
            'parent_post_id' => 'nullable|integer',
            'post' => 'required_without_all:attachment_type|nullable|string',
            'attachment_type' => 'required_without_all:post|nullable|string|in:image,gif,video',
            'attachments' => 'required_with:attachment_type',
        ];

        switch ($this->attachment_type) {
            case 'image':
                $rules['attachments.*'] = 'required|image|mimetypes:' . config('mimetypes.image') . '|max:' . (env('MAX_FILE_SIZE_IMAGE') * 1024);
                break;
            case 'gif':
                $rules['attachments.*'] = 'required|image|mimetypes:' . config('mimetypes.gif') . '|max:' . (env('MAX_FILE_SIZE_IMAGE') * 1024);
                break;
            case 'video':
                $rules['attachments.*'] = 'required|file|mimetypes:' . config('mimetypes.video') . '|max:' . (env('MAX_FILE_SIZE_VIDEO') * 1024);
                break;
        }

        return $rules;
    }
}
