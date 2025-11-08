<?php

namespace App\Http\Requests\Artikel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArtikelRequest extends FormRequest
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
            'kategori_id' => 'sometimes|exists:kategori,id',
            'judul' => 'sometimes|string|max:255',
            'konten' => 'sometimes|string',
            'status' => 'sometimes|in:publikasi,draft',
            'tanggal_publikasi' => 'sometimes|date'
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.exists' => 'Kategori tidak ditemukan.',
            'judul.max' => 'Judul artikel tidak boleh lebih dari 255 karakter.',
            'status.in' => 'Status artikel harus salah satu dari publikasi atau draft.',
            'tanggal_publikasi.required' => 'Tanggal publikasi artikel harus diisi.',
            'tanggal_publikasi.date' => 'Tanggal publikasi artikel harus berupa tanggal.',
        ];
    }
}
