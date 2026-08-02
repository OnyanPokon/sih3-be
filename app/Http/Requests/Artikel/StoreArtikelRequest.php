<?php

namespace App\Http\Requests\Artikel;

use Illuminate\Foundation\Http\FormRequest;

class StoreArtikelRequest extends FormRequest
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
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'thumbnails' => 'required|array',
            'thumbnails.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumen' => 'nullable|array',
            'dokumen.*' => 'nullable|file|mimes:pdf|max:5024',
            'kuesioner_tambahan' => 'required|boolean',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori harus dipilih.',
            'kategori_id.exists' => 'Kategori tidak ditemukan.',
            'judul.required' => 'Judul artikel harus diisi.',
            'judul.max' => 'Judul artikel tidak boleh lebih dari 255 karakter.',
            'konten.required' => 'Konten artikel harus diisi.',
            'thumbnails.*.required' => 'Thumbnail artikel harus diunggah.',
            'thumbnails.*.image' => 'Thumbnail artikel harus berupa gambar.',
            'thumbnails.*.mimes' => 'Thumbnail artikel harus berformat jpeg, png, jpg, atau webp.',
            'thumbnails.*.max' => 'Thumbnail artikel tidak boleh lebih dari 2MB.',
            'dokumen.*.file' => 'Dokumen artikel harus berupa file.',
            'dokumen.*.mimes' => 'Dokumen artikel harus berformat pdf.',
            'dokumen.*.max' => 'Dokumen artikel tidak boleh lebih dari 5MB.',
            'kuesioner_tambahan.required' => 'Kuesioner tambahan harus dipilih.',
        ];
    }
}
