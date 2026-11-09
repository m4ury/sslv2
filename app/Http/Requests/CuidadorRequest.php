<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CuidadorRequest extends FormRequest
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
        $cuidador = $this->route('cuidador'); // null al crear

        $rules = [
            'fecha_ingreso' => ['required', 'date'],
            'telefono' => ['required', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
        ];

        if ($cuidador) {
            // Solo al editar
            $rules['estado'] = ['required', 'string', 'max:255'];
            $rules['fecha_salida'] = ['nullable', 'date', 'after_or_equal:fecha_ingreso'];
        } else {
            // Solo al crear
            $rules['persona_id'] = [
                'required',
                'exists:personas,id',
                Rule::unique('cuidadores', 'persona_id'),
            ];
        }

        return $rules;
    }
}
