<?php

namespace App\Http\Requests\Pemetaan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePemetaanRequest extends FormRequest
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
            'nama_pemetaan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:publikasi,privasi',
            'tipe' => 'required|in:upload,manual',
            'file' => [
                'required_if:tipe,upload|file|max:10240',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['zip', 'geojson', 'rar'])) {
                        $fail('File harus berformat geojson, zip, atau rar.');
                    }
                },
            ],
            'marker' => 'required_if:jenis,point|string',
            'koordinat' => 'required_if:tipe,manual|nullable|json',
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori harus dipilih.',
            'kategori_id.exists' => 'Kategori tidak ditemukan.',
            'file.mimes' => 'File harus berformat geojson, zip, atau rar.',
            'status.in' => 'Status pemetaan harus salah satu dari publikasi atau privasi.',
            'tipe.in' => 'Tipe pemetaan harus salah satu dari upload atau manual.',
            'marker.required_if' => 'Marker pemetaan harus diisi jika jenis pemetaan adalah point.',
            'marker.string' => 'Marker pemetaan harus berupa string.',
        ];
    }
}
