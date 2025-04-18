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
        $taskGroupId = $this->route('tag_group');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($taskGroupId),
            ],
        ];
    }
}
