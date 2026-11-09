<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColaboradorRequest;
use App\Models\Colaborador;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ColaboradorController extends Controller
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

        if ($persona->colaborador) {
            return redirect()->route('colaboradores.show', $persona->colaborador->id)
                ->with('error', 'Esta persona ya está registrada como colaborador.');
        }

        return Inertia::render('colaboradores/Create', [
            'persona' => $persona,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ColaboradorRequest $request)
    {
        $colaborador = Colaborador::create([
            ...$request->validated(),
            'estado' => Colaborador::ESTADO_INICIAL,
        ]);

        return redirect()->route('personas.show', $colaborador->persona_id)
            ->with('success', 'Persona registrada como colaborador.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Colaborador $colaborador)
    {
        return Inertia::render('colaboradores/Show', [
            'colaborador' => $colaborador,
            'persona' => $colaborador->persona,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Colaborador $colaborador)
    {
        return Inertia::render('colaboradores/Edit', [
            'colaborador' => $colaborador,
            'persona' => $colaborador->persona,
            'aportes' => $colaborador->aportes()
                ->with(['usuario:id,name', 'anulador:id,name'])
                ->orderByDesc('fecha_aporte')
                ->orderByDesc('id')
                ->get(),
            'medios' => config('cuotas.medios_pago'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ColaboradorRequest $request, Colaborador $colaborador)
    {
        $colaborador->update($request->validated());

        return redirect()->route('personas.show', $colaborador->persona_id)
            ->with('success', 'Datos del colaborador actualizados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Colaborador $colaborador)
    {
        $personaId = $colaborador->persona_id;

        $colaborador->update([
            'estado' => 'Inactivo',
            'fecha_salida' => now(),
            'observaciones' => 'Colaborador eliminado.',
            'persona_id' => null,
        ]);
    }
}
