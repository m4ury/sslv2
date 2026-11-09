<?php

namespace App\Http\Requests;

use App\Models\Colaborador;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ColaboradorRequest extends FormRequest
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
        $colaborador = $this->route('colaborador');

        $rules = [
            'fecha_ingreso' => ['required', 'date'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:255'],
            'telefono_emergencia_telefono' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ];

        if ($colaborador) {
            // Solo al editar: estado y fecha de salida
            $rules['estado'] = ['required', 'string', 'max:255'];
            $rules['fecha_salida'] = ['nullable', 'date', 'after_or_equal:fecha_ingreso'];
        } else {
            // Solo al crear: persona_id (no se puede cambiar después)
            $rules['persona_id'] = [
                'required',
                'exists:personas,id',
                Rule::unique((new Colaborador)->getTable(), 'persona_id'),
            ];
        }

        return $rules;
    }
}
