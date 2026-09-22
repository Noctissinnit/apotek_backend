<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class GantiPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:password_lama', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.current_password' => 'Password lama salah.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
