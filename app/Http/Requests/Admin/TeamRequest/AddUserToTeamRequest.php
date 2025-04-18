<?php

namespace App\Http\Requests\Admin\TeamRequest;

use Illuminate\Foundation\Http\FormRequest;

class AddUserToTeamRequest extends FormRequest
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
            'team_id' => 'required|exists:teams,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required' => 'Vui lòng chọn nhóm.',
            'team_id.exists' => 'Nhóm không hợp lệ.',
            'user_ids.required' => 'Vui lòng chọn ít nhất một người dùng.',
            'user_ids.*.exists' => 'Người dùng không tồn tại.',
        ];
    }
}
