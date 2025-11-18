<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DokumenArtikelRequest extends FormRequest
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
            'artikel_id' => 'required|exists:artikel,id',
            'nama_dokumen' => 'sometimes|string|max:255',
            'dokumen' => 'required|file|mimes:pdf|max:5024'
        ];
    }

    public function messages(): array
    {
        return [
            'artikel_id.required' => 'Artikel harus dipilih.',
            'artikel_id.exists' => 'Artikel tidak ditemukan.',
            'dokumen.required' => 'Dokumen artikel harus diunggah.',
            'dokumen.file' => 'Dokumen artikel harus berupa file.',
            'dokumen.mimes' => 'Dokumen artikel harus berformat pdf.',
            'dokumen.max' => 'Dokumen artikel tidak boleh lebih dari 5MB.',
        ];
    }
}
