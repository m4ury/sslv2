<?php

namespace App\Http\Requests;

use App\Models\Socio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paciente = $this->route('paciente'); // null al crear

        $rules = [
            'fecha_ingreso' => ['required', 'date'],
            'telefono' => ['required', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ];

        if ($paciente) {
            // Solo al editar
            $rules['estado'] = ['required', 'string', 'max:255'];
            $rules['fecha_salida'] = ['nullable', 'date', 'after_or_equal:fecha_ingreso'];
        } else {
            // Solo al crear
            $rules['persona_id'] = [
                'required',
                'exists:personas,id',
                Rule::unique('pacientes', 'persona_id'),
                /* function ($attribute, $value, $fail) {
                    $esSocioActivo = Socio::where('persona_id', $value)->where('estado', 'Activo')->exists();

                    if (config('pacientes.solo_socios', true) && ! $esSocioActivo) {
                        $fail('Para registrar como paciente, la persona debe ser un socio activo.');
                    }
                }, */
            ];
        }

        return $rules;
    }
}
