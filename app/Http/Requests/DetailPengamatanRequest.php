<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailPengamatanRequest extends FormRequest
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
            'pos_pengamatan_id' => 'required|exists:pos_pengamatan,id',
            'nama' => 'required|string|max:255',
            'koordinat' => 'required|string',
            'alamat' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'pos_pengamatan_id.required' => 'ID pos pengamatan harus diisi.',
            'pos_pengamatan_id.exists' => 'ID pos pengamatan tidak valid.',
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
