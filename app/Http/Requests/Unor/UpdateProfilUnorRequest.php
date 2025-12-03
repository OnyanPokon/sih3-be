<?php

namespace App\Http\Requests\Unor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilUnorRequest extends FormRequest
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
            'nama' => 'sometimes',
            'wilayah' => 'sometimes',
            'alamat' => 'sometimes|string',
            'email' => 'sometimes|email',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'website' => 'sometimes|url',
            'no_hp' => 'sometimes|string',
        ];
    }
}
