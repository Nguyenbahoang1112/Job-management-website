<?php

namespace App\Http\Requests\Admin\TeamRequest;

use Illuminate\Foundation\Http\FormRequest;

class TeamStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:teams,name',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'Tên đội nhóm là bắt buộc.',
            'name.string' => 'Tên đội nhóm phải là chuỗi.',
            'name.max' => 'Tên đội nhóm không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên đội nhóm đã tồn tại.',
        ];
    }
}
