<?php

namespace App\Http\Requests\User\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'due_date'           => 'required|date|date_format:Y-m-d',
            'time'               => 'required|date_format:H:i',
            'tag_ids'            => 'nullable|array',
            'tag_ids.*'          => 'integer|exists:tags,id', // validate từng phần tử trong mảng tag_ids
        ];
    }
}
