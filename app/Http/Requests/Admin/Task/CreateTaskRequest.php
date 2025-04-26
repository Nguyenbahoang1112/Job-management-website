<?php

namespace App\Http\Requests\Admin\Task;

use App\Http\Requests\BaseFormRequest;

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
        // dd(1);
        return [
            'user_id'            => 'required|integer|exists:users,id',
            'title'              => 'required|string|max:255',
            'description'        => 'required|string|max:255',
            'due_date_select'    => 'required|string|in:1,2,3,custom', // nếu có danh sách cố định
            'due_date'           => 'nullable|date|after_or_equal:today',
            'time'               => 'required|date_format:H:i',
            'repeat_type'        => 'required|integer|in:0,1,2,3', // kiểu lặp: không lặp, hằng ngày, v.v...
            'repeat_interval'    => 'nullable|integer|min:1|max:365',
            'repeat_due_date'    => 'nullable|date|after_or_equal:today',
            'tag_ids'            => 'nullable|array',
            'tag_ids.*'          => 'integer|exists:tags,id', // validate từng phần tử trong mảng tag_ids
        ];
    }
}
