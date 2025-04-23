<?php

namespace App\Http\Requests\Admin\TeamRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class TeamUpdateRequest extends FormRequest
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
        $teamId = $this->route('team');
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|max:255',
            Rule::unique('tags', 'name')->ignore($teamId)
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
