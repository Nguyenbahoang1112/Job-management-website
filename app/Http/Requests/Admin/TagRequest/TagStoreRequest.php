<?php

namespace App\Http\Requests\Admin\TagRequest;

use Illuminate\Foundation\Http\FormRequest;

class TagStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:tags,name',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'Tên thẻ là bắt buộc.',
            'name.string' => 'Tên thẻ phải là chuỗi.',
            'name.max' => 'Tên thẻ không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên thẻ đã tồn tại.',
        ];
    }
}
