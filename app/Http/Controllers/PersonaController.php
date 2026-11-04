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
        $rutSearch = str_replace(['.', ' '], '', $search); // permite buscar "12.345.678-9"

        $personas = Persona::query()
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
            'filters' => ['search' => $search],
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
        $persona->load(['socio', 'adulto', 'colaborador']);

        return Inertia::render('personas/Show', [
            'persona' => $persona,
            'roles' => [
                'socio' => $persona->socio?->only('id', 'estado'),
                'adulto' => $persona->adulto?->only('id', 'estado'),
                'colaborador' => $persona->colaborador?->only('id', 'estado'),
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

        return redirect()->route('personas.index')->with('success', 'Persona updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Persona $persona)
    {
        $persona->delete();

        return redirect()->route('personas.index')->with('success', 'Persona deleted successfully.');
    }
}
