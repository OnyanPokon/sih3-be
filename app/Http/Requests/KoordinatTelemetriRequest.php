<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KoordinatTelemetriRequest extends FormRequest
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
            'nilai' => 'required|numeric',
            'tanggal' => 'required|date|date_format:Y-m-d',
            'waktu' => 'required|date_format:H:i',
            'warna' => 'sometimes|string',
        ];
    }

    public function messages()
    {
        return [
            'pos_pengamatan_id.required' => 'Pos pengamatan harus dipilih',
            'pos_pengamatan_id.exists' => 'Pos pengamatan tidak ditemukan',
            'nilai.required' => 'Nilai harus diisi',
            'nilai.numeric' => 'Nilai harus berupa angka',
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date' => 'Tanggal harus berupa tanggal',
            'tanggal.date_format' => 'Tanggal harus berformat YYYY-MM-DD (Contoh : 2023-01-01)',
            'waktu.required' => 'Waktu harus diisi',
            'waktu.date_format' => 'Waktu harus berupa waktu',
            'warna.string' => 'Warna harus berupa string',
        ];
    }
}
