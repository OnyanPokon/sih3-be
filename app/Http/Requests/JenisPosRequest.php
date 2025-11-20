<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JenisPosRequest extends FormRequest
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
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string|max:255',
            'kewenangan' => 'required|string|max:255',
            'thumbnail' => 'nullable|mimes:jpg,png,jpeg|max:2048',
            'status' => 'sometimes|in:publikasi,draft',
        ];
    }

    public function messages()
    {
        return [
            'kategori_id.required' => 'Kategori harus dipilih',
            'kategori_id.exists' => 'Kategori tidak ditemukan',
            'nama.required' => 'Nama pos harus diisi',
            'kewenangan.required' => 'Kewenangan harus diisi',
            'thumbnail.mimes' => 'Thumbnail harus berupa file gambar',
            'thumbnail.max' => 'Thumbnail tidak boleh lebih dari 2MB',
            'status.in' => 'Status harus publikasi atau draft',
        ];
    }
}
