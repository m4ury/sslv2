<?php

namespace App\Http\Controllers;

use App\Http\Requests\PacienteRequest;
use App\Models\Paciente;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    public function create(Request $request)
    {
        $persona = Persona::findOrFail($request->query('persona_id'));

        // Si ya es paciente, se va directo a su edición
        if ($persona->paciente) {
            return redirect()->route('pacientes.edit', $persona->paciente);
        }

        return Inertia::render('pacientes/Create', [
            'persona' => $persona,
        ]);
    }

    public function store(PacienteRequest $request)
    {
        $paciente = Paciente::create([
            ...$request->validated(),
            'estado' => Paciente::ESTADO_INICIAL,
        ]);

        return redirect()->route('personas.show', $paciente->persona_id)
            ->with('success', 'Persona registrada como paciente.');
    }

    public function edit(Paciente $paciente)
    {
        return Inertia::render('pacientes/Edit', [
            'paciente' => $paciente,
            'persona' => $paciente->persona,
        ]);
    }

    public function update(PacienteRequest $request, Paciente $paciente)
    {
        $paciente->update($request->validated());

        return redirect()->route('personas.show', $paciente->persona_id)
            ->with('success', 'Datos del paciente actualizados.');
    }
}
