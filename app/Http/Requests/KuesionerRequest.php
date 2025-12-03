<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KuesionerRequest extends FormRequest
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
            'nama' => 'required',
            'instansi' => 'required',
            'asal' => 'nullable',
            'deskripsi' => 'nullable',
            'nilai' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'artikel_id.required' => 'Artikel harus dipilih.',
            'artikel_id.exists' => 'Artikel tidak ditemukan.',
            'nama.required' => 'Nama harus diisi.',
            'instansi.required' => 'Instansi harus diisi.',
            'nilai.required' => 'Nilai harus diisi.',
            'nilai.integer' => 'Nilai harus berupa angka.',
        ];
    }
}
