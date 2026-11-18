<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonaRequest;
use App\Models\Persona;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type', 'all'); // all, socio, colaborador, cuidador
        $rutSearch = str_replace(['.', ' '], '', $search);

        $puedeVerAportes = $request->user()?->tienePermiso('aportes.ver');

        $personas = Persona::query()
            ->with([
                'socio:id,persona_id,estado',
                'colaborador:id,persona_id,estado',
                'cuidador:id,persona_id,estado',
            ])
            ->when($type !== 'all', function ($query) use ($type, $puedeVerAportes) {
                $query->where(function ($q) use ($type, $puedeVerAportes) {
                    if ($type === 'socio') {
                        $q->has('socio');
                    } elseif ($type === 'colaborador') {
                        if ($puedeVerAportes) {
                            $q->has('colaborador');
                        } else {
                            // Si no tiene permiso, forzar una condición que no devuelva nada
                            // o redirigir/abortar, pero aquí en la query simplemente no match nada
                            $q->whereRaw('1 = 0');
                        }
                    } elseif ($type === 'cuidador') {
                        $q->has('cuidador');
                    }
                });
            })
            ->when($type === 'all' && ! $puedeVerAportes, function ($query) {
                // Si no puede ver aportes y pide "todos", exclude a los que SON colaboradores
                // No deben ser visibles si tienen el rol de colaborador, incluso si tienen otros roles?
                // El requerimiento dice: "solo deberia mostrar socios y cuidadores"
                // Pero luego dijo que falten los que no tienen rol.
                // La interpretación segura: Si no tienes permiso de aportes, NO VES el rol de colaborador.
                // Si la persona es Socio Y Colaborador, el clínico la ve como Socio.
                // Si la persona es SOLO Colaborador, el clínico NO la ve.
                $query->where(function ($q) {
                    $q->has('socio')
                        ->orHas('cuidador')
                        ->orWhere(function ($q2) {
                            $q2->doesntHave('socio')
                                ->doesntHave('cuidador')
                                ->doesntHave('colaborador');
                        });
                });
            })
            ->when($search !== '', function ($query) use ($search, $rutSearch) {
                $query->where(function ($q) use ($search, $rutSearch) {
                    $q->where('rut', 'like', "%{$rutSearch}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$search}%")
                        ->orWhere('apellido_materno', 'like', "%{$search}%");
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('personas/Index', [
            'personas' => $personas,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('personas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonaRequest $request)
    {
        $persona = Persona::create($request->validated());

        return redirect()->route('personas.index')->with('success', 'Persona created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Persona $persona)
    {
        $persona->load(['socio', 'colaborador', 'cuidador']);

        return Inertia::render('personas/Show', [
            'persona' => $persona,
            'roles' => [
                'socio' => $persona->socio?->only('id', 'estado'),
                'colaborador' => $persona->colaborador?->only('id', 'estado'),
                'cuidador' => $persona->cuidador?->only('id', 'estado'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Persona $persona)
    {
        return inertia('personas/Edit', compact('persona'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PersonaRequest $request, Persona $persona)
    {
        $persona->update($request->validated());

        return redirect()->route('personas.index')->with('success', 'Persona actualizada con exito!.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Persona $persona)
    {
        $persona->delete();

        return redirect()->route('personas.index')->with('success', 'Persona eliminada exitosamente!.');
    }
}
