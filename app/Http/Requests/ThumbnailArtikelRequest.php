<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThumbnailArtikelRequest extends FormRequest
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
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'artikel_id.required' => 'Artikel harus dipilih.',
            'artikel_id.exists' => 'Artikel tidak ditemukan.',
            'thumbnail.required' => 'Thumbnail artikel harus diunggah.',
            'thumbnail.image' => 'Thumbnail artikel harus berupa gambar.',
            'thumbnail.mimes' => 'Thumbnail artikel harus berformat jpeg, png, jpg, atau webp.',
            'thumbnail.max' => 'Thumbnail artikel tidak boleh lebih dari 2MB.',
        ];
    }
}
