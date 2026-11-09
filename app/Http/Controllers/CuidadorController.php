<?php

namespace App\Http\Controllers;

use App\Http\Requests\CuidadorRequest;
use App\Models\Cuidador;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CuidadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $persona = Persona::findOrFail($request->query('persona_id'));

        // Si ya es cuidador, se va directo a su edición
        if ($persona->cuidador) {
            return redirect()->route('cuidadores.edit', $persona->cuidador);
        }

        return Inertia::render('cuidadores/Create', [
            'persona' => $persona,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CuidadorRequest $request)
    {
        $cuidador = Cuidador::create([
            ...$request->validated(),
            'estado' => Cuidador::ESTADO_INICIAL,
        ]);

        return redirect()->route('personas.show', $cuidador->persona_id)
            ->with('success', 'Persona registrada como cuidador.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cuidador $cuidador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cuidador $cuidador)
    {
        return Inertia::render('cuidadores/Edit', [
            'cuidador' => $cuidador,
            'persona' => $cuidador->persona,
            'asignaciones' => $cuidador->asignaciones()
                ->with('adulto.persona')
                ->orderByRaw('fecha_fin is not null') // vigentes primero
                ->orderByDesc('fecha_inicio')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CuidadorRequest $request, Cuidador $cuidador)
    {
        $cuidador->update($request->validated());

        return redirect()->route('personas.show', $cuidador->persona_id)
            ->with('success', 'Datos del cuidador actualizados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cuidador $cuidador)
    {
        //
    }
}
