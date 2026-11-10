<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatologiaRequest;
use App\Http\Requests\UpdatePatologiaRequest;
use App\Models\Socio;
use App\Models\Patologia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PatologiaController extends Controller
{
    public function index(Request $request)
    {
        $socio = Socio::with('persona')->find($request->integer('socio'));

        return Inertia::render('catalogos/Patologias', [
            'patologias' => Patologia::withCount('registros')->orderBy('nombre')->get(),
            'volver' => $socio ? [
                'url' => "/socios/{$socio->id}/ficha",
                'nombre' => $socio->persona?->full_name,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        Patologia::create($this->validar($request));

        return back()->with('success', 'Patología agregada al catálogo.');
    }

    public function update(Request $request, Patologia $patologia)
    {
        $patologia->update($this->validar($request, $patologia));

        return back()->with('success', 'Patología actualizada.');
    }

    public function destroy(Patologia $patologia)
    {
        // withTrashed: también cuenta registros eliminados, que siguen ligados por FK
        if ($patologia->registros()->withTrashed()->exists()) {
            return back()->with('error', 'No se puede eliminar: está asociada a registros de socios.');
        }

        $patologia->delete();

        return back()->with('success', 'Patología eliminada del catálogo.');
    }

    private function validar(Request $request, ?Patologia $patologia = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('patologias', 'nombre')->ignore($patologia)],
            /* 'codigo_cie10' => ['nullable', 'string', 'max:10'], */
        ]);
    }
}
