<?php

namespace App\Http\Requests\Personil;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonilRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'nama' => 'required',
            'nip' => 'required',
            'unor_id' => 'required|exists:unor,id',
            'role' => 'required|in:superadmin,admin',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'email.email' => 'Email tidak valid',
            'nama.required' => 'Nama wajib diisi',
            'nip.required' => 'NIP wajib diisi',
            'unor_id.required' => 'Unor wajib diisi',
            'unor_id.exists' => 'Unor tidak ditemukan',
            'role.required' => 'Role wajib diisi',
            'role.in' => 'Role tidak valid',
        ];
    }
}
