<?php

namespace App\Http\Requests\User\Task;

use App\Http\Requests\BaseFormRequest;

class UpdateAllTaskRequest extends BaseFormRequest
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
            'title'              => 'required|string|max:255',
            'description'        => 'required|string|max:255',
            'due_date'           => 'nullable|date|after_or_equal:today|date_format:Y-m-d',
            'time'               => 'required|date_format:H:i',
            'repeat_type'        => 'required|integer|in:1,2,3', // kiểu lặp: không lặp, hằng ngày, v.v...
            'repeat_option'      => 'required|integer|in:1,2',
            'repeat_interval'    => 'nullable|integer|min:1|max:365',
            'repeat_due_date'    => 'nullable|date|after_or_equal:today',
            'tag_ids'            => 'nullable|array',
            'tag_ids.*'          => 'integer|exists:tags,id', // validate từng phần tử trong mảng tag_ids
        ];
    }
}
