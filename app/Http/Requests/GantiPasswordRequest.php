<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GantiPasswordRequest extends FormRequest
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
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:8',
            'konfirmasi_password_baru' => 'required|string|min:8|same:password_baru',
        ];
    }

    public function messages(): array
    {
        return [
            'password_baru.required' => 'Password baru harus diisi.',
            'password_baru.string' => 'Password baru harus berupa string.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'konfirmasi_password_baru.required' => 'Konfirmasi password baru harus diisi.',
            'konfirmasi_password_baru.string' => 'Konfirmasi password baru harus berupa string.',
            'konfirmasi_password_baru.min' => 'Konfirmasi password baru minimal 8 karakter.',
            'konfirmasi_password_baru.same' => 'Konfirmasi password baru harus sama dengan password baru.',
            'password_lama.required' => 'Password lama harus diisi.',
            'password_lama.string' => 'Password lama harus berupa string.',
        ];
    }
}
