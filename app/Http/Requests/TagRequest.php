<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
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
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'warna' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'icon' => 'nullable|string|max:255',
            'kategori_id' => 'required|integer|exists:kategori,id',
            'referensi_type' => 'nullable|string|in:wilayah_sungai,App\Models\WilayahSungai',
            'referensi_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tag wajib diisi.',
            'nama.string' => 'Nama tag harus berupa string.',
            'nama.max' => 'Nama tag maksimal 255 karakter.',
            'warna.regex' => 'Warna harus berupa kode hex yang valid.',
            'icon.string' => 'Icon harus berupa string.',
            'icon.max' => 'Icon maksimal 255 karakter.',
            'kategori_id.required' => 'Kategori wajib diisi.',
            'kategori_id.integer' => 'ID kategori harus berupa angka.',
            'kategori_id.exists' => 'Kategori tidak ditemukan.',
            'referensi_type.in' => 'Tipe referensi tidak valid.',
        ];
    }

}
