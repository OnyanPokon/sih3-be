<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosPengamatanRequest extends FormRequest
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
            'jenis_pos_id' => 'required|exists:jenis_pos,id',
            'nama' => 'required|string|max:255',
            'koordinat' => 'required|string',
            'alamat' => 'required|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_pos_id.required' => 'ID jenis pos harus diisi.',
            'jenis_pos_id.exists' => 'ID jenis pos tidak valid.',
            'nama.required' => 'Nama harus diisi.',
            'nama.string' => 'Nama harus berupa string.',
            'nama.max' => 'Nama maksimal 255 karakter.',
            'koordinat.required' => 'Koordinat harus diisi.',
            'koordinat.string' => 'Koordinat harus berupa string.',
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.string' => 'Alamat harus berupa string.',
        ];
    }
}
