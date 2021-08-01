<?php

namespace Jawabapp\Community\Http\Requests\Community\Post;

use Jawabapp\Community\Models\PostReport;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
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
            'report' => 'required|integer|in:' . implode(',', array_keys(PostReport::REPORT_TYPES)),
        ];
    }
}
