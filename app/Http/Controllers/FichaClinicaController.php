<?php

namespace App\Http\Controllers;

use App\Models\Adulto;
use App\Models\Control;
use App\Models\Patologia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\Cuidador;
use App\Models\Documento;
use App\Models\Prestacion;
use App\Models\Programado;
use App\Models\Tipo_documento;
use Illuminate\Support\Facades\Gate;

class FichaClinicaController extends Controller
{
    public function show(Adulto $adulto)
    {
        return Inertia::render('adultos/Ficha', [
            'adulto' => $adulto->only('id', 'estado', 'fecha_ingreso'),
            'persona' => $adulto->persona,
            //'patologias' => $adulto->patologias()->orderByDesc('fecha_diagnostico')->get(),
            'patologias' => $adulto->patologias()
                ->with('patologia:id,nombre')
                ->orderByDesc('fecha_diagnostico')
                ->get(),
            'catalogoPatologias' => Patologia::orderBy('nombre')->get(['id', 'nombre']),
            'controles' => $adulto->controles()
                ->with(['mediciones', 'user:id,name'])
                ->orderByDesc('created_at')->orderByDesc('id')
                ->get()
                ->map(fn (Control $control) => [
                    'id' => $control->id,
                    'fecha' => $control->fecha_control,
                    'tipo' => $control->tipo_control,
                    'observaciones' => $control->observacion,
                    'mediciones' => $control->mediciones,
                    'autor' => $control->user,
                ]),
            'prestaciones' => $adulto->prestaciones()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Prestacion $prestacion) => [
                    'id' => $prestacion->id,
                    'fecha' => $prestacion->fecha?->toDateString(),
                    'nombre' => $prestacion->nombre_prestacion,
                    'control_id' => $prestacion->control_id,
                    'detalle' => $prestacion->descripcion,
                ]),
            'programados' => $adulto->programados()
                ->orderBy('fecha_programada')
                ->get()
                ->map(fn (Programado $programado) => [
                    'id' => $programado->id,
                    'fecha_programada' => $programado->fecha_programada,
                    'tipo' => $programado->tipo_control,
                    'estado' => $programado->estado,
                    'observaciones' => $programado->observacion,
                ]),
            'credenciales' => $adulto->credenciales()->orderBy('fecha_expiracion')->get(),
            'documentos' => $adulto->documentos()
                ->with('tipoDocumento:id,nombre')
                ->latest()
                ->get()
                ->map(fn (Documento $documento) => [
                    'id' => $documento->id,
                    'nombre' => $documento->nombre,
                    'tipo' => $documento->tipoDocumento->nombre,
                    'fecha_documento' => $documento->fecha_documento?->toDateString(),
                    'tamano' => $documento->tamanio_bytes,
                ]),
            'catalogos' => config('ficha'),
            'cuidadores' => $adulto->cuidadores()
                ->with('cuidador.persona')
                ->orderByDesc('es_principal')
                ->orderByDesc('fecha_inicio')
                ->get(),
            'catalogoCuidadores' => Cuidador::with('persona')
                ->where('persona_id', '!=', $adulto->persona_id) // nadie es cuidador de sí mismo
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->persona->full_name,
                    'rut' => $c->persona->rut,
                    'estado' => $c->estado,
                ])
                ->sortBy('nombre')
                ->values(),
        ]);
    }

    public function store(Request $request, Adulto $adulto, string $seccion)
    {
        Gate::authorize($this->permisoDe($seccion));

        $data = $request->validate($this->reglas($seccion, $adulto, $request, false));

        DB::transaction(function () use ($request, $adulto, $seccion, $data) {
            match ($seccion) {
                'controles' => $this->guardarControl($adulto, $data, $request->user()->id),
                'documentos' => $this->guardarDocumento($request, $adulto, $data),
                'programados' => $adulto->programados()->create([
                    'fecha_programada' => $data['fecha_programada'],
                    'tipo_control' => $data['tipo'],
                    'estado' => 'Pendiente',
                    'observacion' => $data['observaciones'] ?? null,
                ]),
                'prestaciones' => $adulto->controles()
                    ->findOrFail($data['control_id'])
                    ->prestaciones()
                    ->create([
                        'fecha' => $data['fecha'],
                        'nombre_prestacion' => $data['nombre'],
                        'descripcion' => $data['detalle'] ?? null,
                    ]),
                default => $adulto->{$seccion}()->create($data),
            };
        });

        return back()->with('success', 'Registro guardado correctamente.');
    }

    public function update(Request $request, Adulto $adulto, string $seccion, int $id)
    {
        Gate::authorize($this->permisoDe($seccion));
        // Se busca a través de la relación: solo registros de este adulto
        $registro = $adulto->{$seccion}()->findOrFail($id);
        $data = $request->validate($this->reglas($seccion, $adulto, $request, true));

        DB::transaction(function () use ($request, $adulto, $seccion, $registro, $data) {
            if ($seccion === 'controles') {
                $this->guardarControl($adulto, $data, $request->user()->id, $registro);
            } elseif ($seccion === 'programados') {
                $registro->update([
                    'fecha_programada' => $data['fecha_programada'],
                    'tipo_control' => $data['tipo'],
                    'estado' => $data['estado'],
                    'observacion' => $data['observaciones'] ?? null,
                ]);
            } elseif ($seccion === 'prestaciones') {
                $registro->update([
                    'control_id' => $data['control_id'],
                    'fecha' => $data['fecha'],
                    'nombre_prestacion' => $data['nombre'],
                    'descripcion' => $data['detalle'] ?? null,
                ]);
            } elseif ($seccion === 'documentos') {
                $tipoDocumento = Tipo_documento::query()->firstOrCreate([
                    'nombre' => $data['tipo'],
                ]);

                $registro->update([
                    'nombre' => $data['nombre'],
                    'tipo_documento_id' => $tipoDocumento->id,
                    'fecha_documento' => $data['fecha_documento'] ?? null,
                ]);
            } else {
                $registro->update($data);
            }
        });

        return back()->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Adulto $adulto, string $seccion, int $id)
    {
        Gate::authorize($this->permisoDe($seccion));
        
        // Soft delete: el registro deja de mostrarse pero se conserva en la base
        $adulto->{$seccion}()->findOrFail($id)->delete();

        return back()->with('success', 'Registro eliminado.');
    }

    public function descargar(Adulto $adulto, int $documento)
    {
        $doc = $adulto->documentos()->findOrFail($documento);

        // Disco privado: el archivo no es accesible por URL directa
        return Storage::disk('local')->download($doc->archivo_ruta, $doc->archivo_original);
    }

    private function guardarControl(Adulto $adulto, array $data, int $userId, ?Control $control = null): void
    {
        $atributos = [
            'fecha_control' => $data['fecha'],
            'tipo_control' => $data['tipo'],
            'observacion' => $data['observaciones'] ?? null,
        ];

        if ($control) {
            $control->update($atributos);
        } else {
            $control = $adulto->controles()->create([...$atributos, 'user_id' => $userId]);
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
        $tipoDocumento = Tipo_documento::query()->firstOrCreate([
            'nombre' => $data['tipo'],
        ]);

        $adulto->documentos()->create([
            'user_id' => $request->user()->id,
            'nombre' => $data['nombre'],
            'tipo_documento_id' => $tipoDocumento->id,
            'fecha_documento' => $data['fecha_documento'] ?? null,
            'archivo_ruta' => $archivo->store("fichas/{$adulto->id}", 'local'),
            'archivo_original' => $archivo->getClientOriginalName(),
            'mime_type' => $archivo->getMimeType(),
            'tamanio_bytes' => $archivo->getSize(),
        ]);
    }

    private function reglas(string $seccion, Adulto $adulto, Request $request, bool $editando): array
    {
        $c = config('ficha');
        $vigente = in_array($request->input('estado'), ['Activa', 'Controlada'], true);
        $existeCuidador = Rule::exists('cuidadores', 'id');
        if (!$editando) {
            // Solo se pueden asignar cuidadores activos; al editar se admite cualquiera
            $existeCuidador->where('estado', 'Activo');
        }

        return match ($seccion) {
            'patologias' => [
            'patologia_id' => [
                'required',
                Rule::exists('patologias', 'id'),
                // Evita dos registros vigentes de la misma patología en el mismo adulto
                ...($vigente ? [
                    Rule::unique('adulto_patologia', 'patologia_id')
                        ->where('adulto_id', $adulto->id)
                        ->whereNull('deleted_at')
                        ->whereIn('estado', ['Activa', 'Controlada'])
                        ->ignore($request->route('id')),
                ] : []),
            ],
            'fecha_diagnostico' => ['nullable', 'date', 'before_or_equal:today'],
            'estado' => ['required', Rule::in($c['estados_patologia'])],
            'observaciones' => ['nullable', 'string'],
            ],

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
                    'required',
                    Rule::exists('controls', 'id')
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
                'tipo_discapacidad' => ['required', Rule::in($c['tipo_discapacidad'])],
                'grado_discapacidad' => ['nullable'],
                'porcentaje_discapacidad' => ['nullable', 'numeric', 'between:0,100'],
                'numero' => ['nullable', 'string', 'max:255'],
                'organismo_emisor' => ['nullable', 'string', 'max:255'],
                'fecha_emision' => ['nullable', 'date'],
                'fecha_expiracion' => [
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

            'cuidadores' => [
                'cuidador_id' => [
                    'required',
                    $existeCuidador,
                    function ($attribute, $value, $fail) use ($adulto) {
                        if (Cuidador::whereKey($value)->where('persona_id', $adulto->persona_id)->exists()) {
                            $fail('Una persona no puede ser cuidadora de sí misma.');
                        }
                    },
                    // Evita dos asignaciones vigentes del mismo cuidador al mismo adulto
                    ...($request->filled('fecha_fin') ? [] : [
                        Rule::unique('adulto_cuidador', 'cuidador_id')
                            ->where('adulto_id', $adulto->id)
                            ->whereNull('deleted_at')
                            ->whereNull('fecha_fin')
                            ->ignore($request->route('id')),
                    ]),
                ],
                'vinculo' => ['nullable', 'string', 'max:255'],
                'es_principal' => ['required', 'boolean'],
                'fecha_inicio' => ['required', 'date'],
                'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
                'observaciones' => ['nullable', 'string'],
            ],
        };
    }

    private const PERMISO_POR_SECCION = [
        'cuidadores' => 'cuidadores.asignar',
    ];

    private function permisoDe(string $seccion): string
    {
        return self::PERMISO_POR_SECCION[$seccion] ?? 'ficha.registrar';
    }
}
