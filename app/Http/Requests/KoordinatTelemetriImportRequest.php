<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KoordinatTelemetriImportRequest extends FormRequest
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
            'file' => 'required|mimes:xlsx',
            'pos_pengamatan_id' => 'required|exists:pos_pengamatan,id',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'File harus diisi',
            'file.mimes' => 'File harus berupa file Excel',
            'pos_pengamatan_id.required' => 'Pos pengamatan harus dipilih',
            'pos_pengamatan_id.exists' => 'Pos pengamatan tidak ditemukan',
        ];
    }
}
