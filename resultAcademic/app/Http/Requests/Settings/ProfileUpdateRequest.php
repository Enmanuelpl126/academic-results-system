<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Campos adicionales permitidos en el perfil
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'ci' => ['nullable', 'string', 'regex:/^[0-9]{11}$/', Rule::unique(User::class, 'ci')->ignore($this->user()->id)],
            'teaching_category' => ['nullable', 'string', Rule::in(['profesor_principal', 'profesor_ayudante', 'profesor_entrenador'])],
            'scientific_category' => ['nullable', 'string', Rule::in(['aspirante', 'investigador_agregado', 'investigador_titular'])],
            'professional_level' => ['required', Rule::in(['tecnico','especialista','obrero','licenciado','master','doctor_en_ciencias'])],
        ];
    }
}
