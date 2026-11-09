<?php

namespace App\Http\Controllers;

use App\Models\Profesion;
use App\Models\User;
use App\Support\Permisos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfesionController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Profesiones', [
            'profesiones' => Profesion::withCount('usuarios')->orderBy('nombre')->get(),
            'grupos' => config('permisos.grupos'),
            'plantillas' => config('permisos.plantillas'),
        ]);
    }

    public function store(Request $request)
    {
        Profesion::create($this->validar($request));

        return back()->with('success', 'Profesión creada.');
    }

    public function update(Request $request, Profesion $profesion)
    {
        $data = $this->validar($request, $profesion);

        // No se puede quitar el rol de administrador si no queda otro administrador activo
        if ($profesion->es_admin && ! $data['es_admin'] && ! $this->hayOtroAdmin($profesion)) {
            return back()->with('error', 'Debe quedar al menos un administrador activo.');
        }

        $profesion->update($data);

        return back()->with('success', 'Profesión actualizada.');
    }

    public function destroy(Profesion $profesion)
    {
        if ($profesion->usuarios()->exists()) {
            return back()->with('error', 'No se puede eliminar: tiene usuarios asignados.');
        }

        $profesion->delete();

        return back()->with('success', 'Profesión eliminada.');
    }

    private function validar(Request $request, ?Profesion $profesion = null): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('profesiones', 'nombre')->ignore($profesion)],
            'es_admin' => ['required', 'boolean'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', Rule::in(Permisos::claves())],
        ]);

        $data['permisos'] = array_values($data['permisos'] ?? []);

        return $data;
    }

    private function hayOtroAdmin(Profesion $excluir): bool
    {
        return User::where('activo', true)
            ->whereHas('profesion', fn ($q) => $q->where('es_admin', true)->whereKeyNot($excluir->id))
            ->exists();
    }
}