<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BeritaRequest extends FormRequest
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
            'judul' => 'required|string|max:255',
            'konten' => 'required',
            'thumbnail' => 'nullable|mimes:jpg,png,jpeg|max:2048',
            'kategori_id' => 'required|exists:kategori,id',
            'status' => 'sometimes|in:publikasi,draft',
        ];
    }

    public function messages()
    {
        return [
            'judul.required' => 'Judul harus diisi',
            'konten.required' => 'Konten harus diisi',
            'thumbnail.mimes' => 'Thumbnail harus berformat JPG, PNG, atau JPEG',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB',
            'kategori_id.required' => 'Kategori harus diisi',
            'kategori_id.exists' => 'Kategori tidak valid',
            'status.in' => 'Status harus salah satu dari publikasi atau draft',
        ];
    }
}
