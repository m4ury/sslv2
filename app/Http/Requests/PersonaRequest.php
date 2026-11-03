<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonaRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $persona = $this->route('persona');
        
        return [
            'rut' => ['required', 'string', 'max:12', Rule::unique('personas', 'rut')->ignore($persona)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('personas', 'email')->ignore($persona)],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo' => ['required', Rule::in(['masculino', 'femenino'])],
        ];
    }
}
