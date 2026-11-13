<?php

namespace App\Http\Controllers;

use App\Services\InformesIngresos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InformeIngresosController extends Controller
{
    public function __construct(private InformesIngresos $informes) {}

    public function mostrar(string $libro, ?string $periodo = null)
    {
        $periodo ??= now()->format('Y-m');

        return Inertia::render('informes/Mensual', [
            ...$this->datos($libro, $periodo),
            'baseUrl' => $this->base($libro),
            'volverA' => $libro === 'aportes' ? '/aportes' : '/banco',
        ]);
    }

    public function pdf(Request $request, string $libro, string $periodo)
    {
        return Pdf::loadView('informes.mensual', [
            'informe' => $this->datos($libro, $periodo),
            'agrupacion' => config('app.name'),
            'generadoPor' => $request->user()->name,
            'generadoEn' => now(),
        ])->setPaper('a4')->download("informe-{$libro}-{$periodo}.pdf");
    }

    private function datos(string $libro, string $periodo): array
    {
        return match ($libro) {
            'aportes' => $this->informes->aportes($periodo),
            'banco' => $this->informes->banco($periodo),
            default => abort(404),
        };
    }

    private function base(string $libro): string
    {
        return $libro === 'aportes' ? '/aportes/informe' : '/banco/informe';
    }
}