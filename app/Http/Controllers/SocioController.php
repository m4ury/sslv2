<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocioRequest;
use App\Models\Socio;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;


class SocioController extends Controller
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

        if ($persona->socio) {
            return redirect()->route('socios.show', $persona->socio->id)
                ->with('error', 'Esta persona ya está registrada como socio.');
        }

        return Inertia::render('socios/Create', [
            'persona' => $persona,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SocioRequest $request)
    {
        $socio = Socio::create([
            ...$request->validated(),
            'estado' => Socio::ESTADO_INICIAL,
        ]);

        return redirect()->route('personas.show', $socio->persona_id)
            ->with('success', 'Persona registrada como socio.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Socio $socio)
    {
        return Inertia::render('socios/Show', [
            'socio' => $socio,
            'persona' => $socio->persona,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Socio $socio)
    {

        return Inertia::render('socios/Edit', [
            'socio' => $socio,
            'persona' => $socio->persona,
            'cuotas' => Gate::allows('cuotas.ver')
                ? $socio->cuotas()
                ->with([
                    'socio.persona', // los diálogos muestran el nombre del socio
                    'configuracion:id,nombre_cuota',
                    'pagos' => fn ($q) => $q
                        ->with(['usuario:id,name', 'anulador:id,name'])
                        ->orderByDesc('fecha_pago')
                        ->orderByDesc('id'),
                ])
                ->orderByDesc('periodo')
                ->get()
                :[],
            'medios' => config('cuotas.medios_pago'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SocioRequest $request, Socio $socio)
    {
        $socio->update($request->validated());

        return redirect()->route('personas.show', $socio->persona_id)
        ->with('success', 'Datos del socio actualizados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Socio $socio)
    {
        //
    }

    public function baja(Socio $socio)
    {
        if (! $socio->estaActivo()) {
            return back()->with('error', 'El socio no está activo.');
        }

        $socio->darDeBaja();

        return redirect()->route('personas.show', $socio->persona_id)
            ->with('success', 'Socio dado de baja. Los datos de la persona se mantienen.');
    }

    public function reactivar(Socio $socio)
    {
        if (! $socio->estaInactivo()) {
            return back()->with('error', 'Solo se puede reingresar a un socio inactivo.');
        }

        $socio->reactivar();

        return redirect()->route('personas.show', $socio->persona_id)
            ->with('success', 'Socio reingresado correctamente.');
    }
}
