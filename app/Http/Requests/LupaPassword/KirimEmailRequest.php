<?php

namespace App\Http\Requests\LupaPassword;

use Illuminate\Foundation\Http\FormRequest;

class KirimEmailRequest extends FormRequest
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
            'email' => 'required|email|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            // Validasi email
            'email.required' => 'Email harus diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.min'      => 'Email minimal harus 5 karakter.',
            'email.max'      => 'Email terlalu panjang (maksimal 100 karakter).',
        ];
    }
}
