<?php

namespace App\Http\Requests\Admin\TaskGroupRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class TaskGroupUpdateRequest extends FormRequest
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
        $taskGroupId = $this->route('task_group');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('task_groups', 'name')->ignore($taskGroupId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên nhóm công việc là bắt buộc.',
            'name.string' => 'Tên nhóm công việc phải là chuỗi.',
            'name.max' => 'Tên nhóm công việc không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên nhóm công việc đã tồn tại.',
        ];
    }
}
