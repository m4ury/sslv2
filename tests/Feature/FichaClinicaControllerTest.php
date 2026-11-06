<?php

use App\Models\Adulto;
use App\Models\Control;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('saving a clinical control records its author and measurements', function () {
    $user = User::factory()->create();
    $persona = Persona::query()->create([
        'rut' => '12.345.678-5',
        'nombres' => 'Ana',
        'apellido_paterno' => 'Perez',
    ]);
    $adulto = Adulto::query()->create([
        'persona_id' => $persona->id,
        'fecha_ingreso' => '2026-01-01',
    ]);

    $this->actingAs($user)
        ->post(route('ficha.store', ['adulto' => $adulto, 'seccion' => 'controles']), [
            'fecha' => today()->toDateString(),
            'tipo' => 'Control médico',
            'observaciones' => 'Seguimiento general',
            'med_pa_sistolica' => 120,
            'med_pa_diastolica' => 80,
            'med_peso' => 70.5,
        ])
        ->assertRedirect();

    $control = Control::query()->sole();

    $this->assertDatabaseHas('controls', [
        'id' => $control->id,
        'adulto_id' => $adulto->id,
        'user_id' => $user->id,
        'fecha_control' => today()->toDateString(),
        'tipo_control' => 'Control médico',
        'observacion' => 'Seguimiento general',
    ]);
    $this->assertDatabaseCount('medicions', 3);
    $this->assertDatabaseHas('medicions', [
        'control_id' => $control->id,
        'tipo' => 'pa_sistolica',
        'valor' => 120,
        'unidad' => 'mmHg',
    ]);

    $this->get(route('ficha.show', ['adulto' => $adulto]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adultos/Ficha')
            ->where('controles.0.tipo', 'Control médico')
            ->where('controles.0.observaciones', 'Seguimiento general')
            ->where('controles.0.autor.name', $user->name)
            ->has('controles.0.mediciones', 3),
        );
});
