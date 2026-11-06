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
            'rut' => ['required', 'string', 'max:12', Rule::unique('personas', 'rut')->ignore($persona), 'cl_rut'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'email' => ['nullable', Rule::unique('personas', 'email')->ignore($persona)],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo' => ['required', Rule::in(['Masculino', 'Femenino'])],
            'direccion' => ['required', 'string', 'max:255'],
            'comuna' => [
                'required',
                Rule::in([
                    'Cauquenes',
                    'Chanco',
                    'Pelluhue',
                    'Curico',
                    'Hualane',
                    'Licanten',
                    'Molina',
                    'Rauco',
                    'Romeral',
                    'Sgda Familia',
                    'Teno',
                    'Vichuquen',
                    'Linares',
                    'Colbun',
                    'Longabi',
                    'Parral',
                    'Retiro',
                    'San Javier',
                    'Villa Alegre',
                    'Yerbas Buenas',
                    'Talca',
                    'Constitucion',
                    'Empedrado',
                    'Maule',
                    'Pelarco',
                    'Pencahue',
                    'Rio Claro',
                    'San Clemente',
                    'San Rafael',
                    'Curepto',
                ]),
            ],
        ];
    }
}
