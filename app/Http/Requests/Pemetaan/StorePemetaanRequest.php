<?php

namespace App\Http\Requests\Pemetaan;

use Illuminate\Foundation\Http\FormRequest;

class StorePemetaanRequest extends FormRequest
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
            'nama_pemetaan' => 'required',
            'deskripsi' => 'sometimes|nullable',
            'tipe' => 'required|in:upload,manual',
            'jenis' => 'required|in:point,polygon,polyline',
            'file' => [
                'required_if:tipe,upload|file|max:10240',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['zip', 'geojson', 'rar'])) {
                        $fail('File harus berformat geojson, zip, atau rar.');
                    }
                },
            ],
            'koordinat' => 'required_if:tipe,manual|nullable|array',
            'status' => 'required|in:publikasi,privasi',
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori harus dipilih.',
            'kategori_id.exists' => 'Kategori tidak ditemukan.',
            'file.mimes' => 'File harus berformat geojson, zip, atau rar.',
            'tipe.in' => 'Tipe pemetaan harus salah satu dari upload atau manual.',
            'koordinat.required_if' => 'Koordinat pemetaan harus diisi jika tipe pemetaan adalah manual.',
            'koordinat.array' => 'Koordinat pemetaan harus berupa array.',
            'jenis.required' => 'Jenis pemetaan harus dipilih.',
            'jenis.in' => 'Jenis pemetaan harus salah satu dari point, polygon, atau polyline.',
            'file.required_if' => 'File pemetaan harus diunggah jika tipe pemetaan adalah upload.',
            'nama_pemetaan.required' => 'Nama pemetaan harus diisi.',
            'status.required' => 'Status pemetaan harus dipilih.',
            'status.in' => 'Status pemetaan harus salah satu dari publikasi atau privasi.',
        ];
    }
}
