<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdultoRequest;
use App\Models\Adulto;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdultoController extends Controller
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

        // Si ya es adulto, no tiene sentido crear otro registro
        if ($persona->adulto) {
            return redirect()->route('adultos.show', $persona->adulto->id)
                ->with('error', 'Esta persona ya está registrada como adulto.');
        }

        return Inertia::render('adultos/Create', [
            'persona' => $persona,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdultoRequest $request)
    {
            $adulto = Adulto::create([
            ...$request->validated(),
            'estado' => Adulto::ESTADO_INICIAL,
        ]);

        return redirect()->route('personas.show', $adulto->persona_id)
            ->with('success', 'Persona registrada como adulto.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Adulto $adulto)
    {
        return Inertia::render('adultos/Show', [
            'adulto' => $adulto,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adulto $adulto)
    {
        return Inertia::render('adultos/Edit', [
        'adulto' => $adulto,
        'persona' => $adulto->persona,
    ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdultoRequest $request, Adulto $adulto)
    {
        $adulto->update($request->validated());

        return redirect()->route('personas.show', $adulto->persona_id)
        ->with('success', 'Datos del adulto actualizados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adulto $adulto)
    {
        $adulto->delete();

        return redirect()->route('personas.index')->with('success', 'Adulto deleted successfully.');
    }

    public function baja(Adulto $adulto)
    {
        if (! $adulto->estaActivo()) {
            return back()->with('error', 'El adulto no está activo.');
        }

        $adulto->darDeBaja();

        return redirect()->route('personas.show', $adulto->persona_id)
            ->with('success', 'Adulto dado de baja. Los datos de la persona se mantienen.');
    }

    public function reactivar(Adulto $adulto)
    {
        if (! $adulto->estaInactivo()) {
            return back()->with('error', 'Solo se puede reingresar a un adulto inactivo.');
        }

        $adulto->reactivar();

        return redirect()->route('personas.show', $adulto->persona_id)
            ->with('success', 'Adulto reingresado correctamente.');
    }
}
