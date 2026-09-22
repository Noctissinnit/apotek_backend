<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => [$wajib, 'string', 'max:100'],
            'email' => [$wajib, 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            // Saat update, password opsional: kosongkan kalau tidak ingin diganti.
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', Password::min(8)->letters()->numbers()],
            'role' => [$wajib, Rule::in(User::ROLES)],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah dipakai user lain.',
            'role.in' => 'Role harus salah satu dari: '.implode(', ', User::ROLES).'.',
        ];
    }
}
