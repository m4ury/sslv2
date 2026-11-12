<?php

namespace App\Http\Controllers;

use App\Models\Programado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'estado' => ['nullable', Rule::in(config('ficha.estados_programado'))],
            'tipo' => ['nullable', Rule::in(config('ficha.tipos_control'))],
        ]);

        $desde = Carbon::parse($request->query('desde', now()->startOfWeek()->toDateString()))->startOfDay();
        $hasta = Carbon::parse($request->query('hasta', now()->endOfWeek()->toDateString()))->startOfDay();

        // Se acota el rango para que una consulta no cargue meses de datos
        if ($hasta->lt($desde)) {
            $hasta = $desde->copy();
        }
        if ($hasta->gt($desde->copy()->addDays(62))) {
            $hasta = $desde->copy()->addDays(62);
        }

        $items = Programado::with('socio.persona')
            ->whereBetween('fecha_programada', [$desde->toDateString(), $hasta->toDateString()])
            ->when(
                $request->filled('estado'),
                fn ($q) => $q->where('estado', $request->query('estado')),
                fn ($q) => $q->where('estado', '!=', 'Cancelado'), // por defecto no se muestran cancelados
            )
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo_control', $request->query('tipo')))
            ->orderBy('fecha_programada')
            ->orderBy('socio_id')
            ->get();

        // Formato de evento de FullCalendar. Sin observaciones: son notas clínicas.
        $eventos = $items->map(function (Programado $p) {
            $dia = $p->fecha_programada->toDateString();
            $persona = $p->socio?->persona;

            $evento = [
                'id' => $p->id,
                'title' => $persona?->full_name ?? 'Socio',
                'allDay' => $p->hora === null,
                'start' => $p->hora ? "{$dia}T{$p->hora}:00" : $dia,
                'extendedProps' => [
                    'socio_id' => $p->socio_id,
                    'rut' => $persona?->rut,
                    'tipo' => $p->tipo_control,
                    'estado' => $p->estado,
                    'hora' => $p->hora,
                    'duracion' => $p->duracion_min,
                ],
            ];

            if ($p->hora) {
                $evento['end'] = Carbon::parse("{$dia} {$p->hora}")
                    ->addMinutes($p->duracion_min)
                    ->format('Y-m-d\TH:i:s');
            }

            return $evento;
        })->values();

        return Inertia::render('agenda/Index', [
            'eventos' => $eventos,
            'rango' => ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()],
            'resumen' => [
                'total' => $items->count(),
                'pendientes' => $items->where('estado', 'Pendiente')->count(),
                'horas' => round($items->sum('duracion_min') / 60, 1),
            ],
            'filters' => $request->only('estado', 'tipo'),
            'estados' => config('ficha.estados_programado'),
            'tipos' => config('ficha.tipos_control'),
        ]);
    }
}
