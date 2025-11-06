<?php

namespace App\Http\Requests\Unor;

use Illuminate\Foundation\Http\FormRequest;

class UnorRequest extends FormRequest
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
            'nama' => 'required',
            'wilayah' => 'required',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'website' => 'nullable|url',
            'no_hp' => 'nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama harus diisi.',
            'wilayah.required' => 'Wilayah harus diisi.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Format logo harus jpeg, png, jpg, gif, atau svg.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
        ];
    }
}
