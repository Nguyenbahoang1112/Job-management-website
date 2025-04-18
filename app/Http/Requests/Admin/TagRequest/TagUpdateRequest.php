<?php

namespace App\Http\Requests\Admin\TagRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class TagUpdateRequest extends FormRequest
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
        $tagId = $this->route('tag');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($tagId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên tag là bắt buộc.',
            'name.string' => 'Tên tag phải là chuỗi.',
            'name.max' => 'Tên tag không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên tag đã tồn tại.',
        ];
    }
}
