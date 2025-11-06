<?php

namespace App\Http\Requests\Personil;

use App\Models\Personil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilPersonilRequest extends FormRequest
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
        $personilId = $this->route('id');
        $personil = Personil::find($personilId);
        $userId = $personil ? $personil->user->id : null;

        return [
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'nama' => 'sometimes',
            'nip' => 'sometimes',
            'jabatan' => 'sometimes',
            'no_hp' => 'sometimes',
        ];
    }
}
