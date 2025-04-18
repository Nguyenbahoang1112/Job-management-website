<?php

namespace App\Http\Requests\Admin\TaskGroupRequest;

use Illuminate\Foundation\Http\FormRequest;

class TaskGroupStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:task_groups,name',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'Tên nhóm công việc là bắt buộc.',
            'name.string' => 'Tên nhóm công việc phải là chuỗi.',
            'name.max' => 'Tên nhóm công việc không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên nhóm công việc đã tồn tại.',
        ];
    }
}
