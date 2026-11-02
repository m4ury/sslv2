<?php

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('persona index includes the full name attribute', function () {
    $this->travelTo('2026-11-02');

    Persona::query()->create([
        'nombres' => 'Ana Maria',
        'rut' => '12.345.678-5',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Gonzalez',
        'email' => 'ana@example.com',
        'fecha_nacimiento' => '1990-01-01',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('personas.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('personas/Index')
            ->where('personas.0.full_name', 'ANA MARIA PEREZ GONZALEZ')
            ->where('personas.0.edad', 36),
        );
});

test('persona date of birth and sex can be updated', function () {
    $persona = Persona::query()->create([
        'nombres' => 'Ana Maria',
        'rut' => '12.345.678-5',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Gonzalez',
        'email' => 'ana@example.com',
        'fecha_nacimiento' => '1990-01-01',
        'sexo' => 'femenino',
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('personas.update', $persona), [
            'nombres' => 'Ana Maria',
            'rut' => '12.345.678-5',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gonzalez',
            'email' => 'ana@example.com',
            'fecha_nacimiento' => '1992-06-15',
            'sexo' => 'masculino',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('personas.index'));

    $this->assertDatabaseHas('personas', [
        'id' => $persona->id,
        'fecha_nacimiento' => '1992-06-15',
        'sexo' => 'masculino',
    ]);
});

test('persona creation rejects an invalid chilean rut', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('personas.create'))
        ->followingRedirects()
        ->post(route('personas.store'), [
            'nombres' => 'Ana Maria',
            'rut' => '12.345.678-9',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gonzalez',
            'email' => 'ana@example.com',
        ])
        ->assertInertia(fn (Assert $page) => $page
            ->component('personas/Create')
            ->where('errors.rut', 'El campo rut debe ser un R.U.T. válido.'),
        );

    $this->assertDatabaseCount('personas', 0);
});

test('persona creation returns a validation error for a duplicate rut', function () {
    Persona::query()->create([
        'nombres' => 'Ana Maria',
        'rut' => '12.345.678-5',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Gonzalez',
        'email' => 'ana@example.com',
    ]);

    $this->actingAs(User::factory()->create())
        ->from(route('personas.create'))
        ->followingRedirects()
        ->post(route('personas.store'), [
            'nombres' => 'Maria',
            'rut' => '12.345.678-5',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Diaz',
            'email' => 'maria@example.com',
        ])
        ->assertInertia(fn (Assert $page) => $page
            ->component('personas/Create')
            ->where('errors.rut', 'The rut has already been taken.'),
        );

    $this->assertDatabaseCount('personas', 1);
});
