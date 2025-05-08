<?php

namespace App\Http\Requests\User\Task;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends BaseFormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_id'           => 'nullable|integer|exists:task_groups,id',
            'title'              => 'required|string|max:255',
            'description'        => 'required|string|max:255',
            'due_date_select'    => 'required|integer|in:1,2,3,4', // nếu có danh sách cố định
            'due_date'           => 'nullable|date|after_or_equal:today',
            'time'               => 'required|date_format:H:i',
            'repeat_type'        => 'required|integer|in:0,1,2,3', // kiểu lặp: không lặp, hằng ngày, v.v...
            'repeat_option'      => 'nullable|integer|in:1,2',
            'repeat_interval'    => 'nullable|integer|min:1|max:365',
            'repeat_due_date'    => 'nullable|date|after_or_equal:today',
            'tag_ids'            => 'nullable|array',
            'tag_ids.*'          => 'integer|exists:tags,id', // validate từng phần tử trong mảng tag_ids
        ];
    }
}
