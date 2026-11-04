<?php

namespace App\Http\Controllers;

use App\Models\Adulto;
use App\Models\Control;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FichaClinicaController extends Controller
{
    public function show(Adulto $adulto)
    {
        return Inertia::render('adultos/Ficha', [
            'adulto' => $adulto->only('id', 'estado', 'fecha_ingreso'),
            'persona' => $adulto->persona,
            'patologias' => $adulto->patologias()->orderByDesc('fecha_diagnostico')->get(),
            'controles' => $adulto->controles()
                ->with(['mediciones', 'autor:id,name'])
                ->orderByDesc('fecha')->orderByDesc('id')
                ->get(),
            'prestaciones' => $adulto->prestaciones()->orderByDesc('fecha')->get(),
            'programados' => $adulto->programados()->orderBy('fecha_programada')->get(),
            'credenciales' => $adulto->credenciales()->orderBy('fecha_vencimiento')->get(),
            'documentos' => $adulto->documentos()->latest()->get(),
            'catalogos' => config('ficha'),
        ]);
    }

    public function store(Request $request, Adulto $adulto, string $seccion)
    {
        $data = $request->validate($this->reglas($seccion, $adulto, $request, false));

        DB::transaction(function () use ($request, $adulto, $seccion, $data) {
            match ($seccion) {
                'controles' => $this->guardarControl($adulto, $data),
                'documentos' => $this->guardarDocumento($request, $adulto, $data),
                'programados' => $adulto->programados()->create([...$data, 'estado' => 'Pendiente']),
                default => $adulto->{$seccion}()->create($data),
            };
        });

        return back()->with('success', 'Registro guardado correctamente.');
    }

    public function update(Request $request, Adulto $adulto, string $seccion, int $id)
    {
        // Se busca a través de la relación: solo registros de este adulto
        $registro = $adulto->{$seccion}()->findOrFail($id);
        $data = $request->validate($this->reglas($seccion, $adulto, $request, true));

        DB::transaction(function () use ($adulto, $seccion, $registro, $data) {
            if ($seccion === 'controles') {
                $this->guardarControl($adulto, $data, $registro);
            } else {
                $registro->update($data);
            }
        });

        return back()->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Adulto $adulto, string $seccion, int $id)
    {
        // Soft delete: el registro deja de mostrarse pero se conserva en la base
        $adulto->{$seccion}()->findOrFail($id)->delete();

        return back()->with('success', 'Registro eliminado.');
    }

    public function descargar(Adulto $adulto, int $documento)
    {
        $doc = $adulto->documentos()->findOrFail($documento);

        // Disco privado: el archivo no es accesible por URL directa
        return Storage::disk('local')->download($doc->ruta, $doc->nombre_original);
    }

    private function guardarControl(Adulto $adulto, array $data, ?Control $control = null): void
    {
        $atributos = Arr::only($data, ['fecha', 'tipo', 'observaciones']);

        if ($control) {
            $control->update($atributos);
        } else {
            $control = $adulto->controles()->create($atributos);
        }

        $control->mediciones()->delete();

        foreach (config('ficha.mediciones') as $tipo => $def) {
            $valor = $data["med_{$tipo}"] ?? null;

            if ($valor !== null && $valor !== '') {
                $control->mediciones()->create([
                    'tipo' => $tipo,
                    'valor' => $valor,
                    'unidad' => $def['unidad'],
                ]);
            }
        }
    }

    private function guardarDocumento(Request $request, Adulto $adulto, array $data): void
    {
        $archivo = $request->file('archivo');

        $adulto->documentos()->create([
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'fecha_documento' => $data['fecha_documento'] ?? null,
            'ruta' => $archivo->store("fichas/{$adulto->id}", 'local'),
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $archivo->getMimeType(),
            'tamano' => $archivo->getSize(),
        ]);
    }

    private function reglas(string $seccion, Adulto $adulto, Request $request, bool $editando): array
    {
        $c = config('ficha');

        return match ($seccion) {
            /* 'patologias' => [
                'nombre' => ['required', 'string', 'max:255'],
                'codigo_cie10' => ['nullable', 'string', 'max:10'],
                'fecha_diagnostico' => ['nullable', 'date', 'before_or_equal:today'],
                'estado' => ['required', Rule::in($c['estados_patologia'])],
                'observaciones' => ['nullable', 'string'],
            ], */

            'controles' => [
                'fecha' => ['required', 'date', 'before_or_equal:today'],
                'tipo' => ['required', Rule::in($c['tipos_control'])],
                'observaciones' => ['nullable', 'string'],
                ...collect($c['mediciones'])->mapWithKeys(fn ($def, $tipo) => [
                    "med_{$tipo}" => ['nullable', 'numeric', "between:{$def['min']},{$def['max']}"],
                ])->all(),
            ],

            'prestaciones' => [
                'fecha' => ['required', 'date', 'before_or_equal:today'],
                'nombre' => ['required', 'string', 'max:255'],
                'control_id' => [
                    'nullable',
                    Rule::exists('controles', 'id')
                        ->where('adulto_id', $adulto->id)
                        ->whereNull('deleted_at'),
                ],
                'detalle' => ['nullable', 'string'],
            ],

            'programados' => [
                'fecha_programada' => ['required', 'date', ...($editando ? [] : ['after_or_equal:today'])],
                'tipo' => ['required', Rule::in($c['tipos_control'])],
                'observaciones' => ['nullable', 'string'],
                ...($editando ? ['estado' => ['required', Rule::in($c['estados_programado'])]] : []),
            ],

            'credenciales' => [
                'tipo' => ['required', Rule::in($c['tipos_credencial'])],
                'numero' => ['nullable', 'string', 'max:255'],
                'emisor' => ['nullable', 'string', 'max:255'],
                'fecha_emision' => ['nullable', 'date'],
                'fecha_vencimiento' => [
                    'nullable',
                    'date',
                    ...($request->filled('fecha_emision') ? ['after_or_equal:fecha_emision'] : []),
                ],
                'observaciones' => ['nullable', 'string'],
            ],

            'documentos' => [
                'nombre' => ['required', 'string', 'max:255'],
                'tipo' => ['required', Rule::in($c['tipos_documento'])],
                'fecha_documento' => ['nullable', 'date', 'before_or_equal:today'],
                ...($editando ? [] : [
                    'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
                ]),
            ],
        };
    }
}
