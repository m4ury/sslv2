<?php

namespace Database\Seeders;

use App\Models\User;
use App\Model\Persona;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'usuario',
            'rut' => '12345678-9',
            'apellido_paterno' => 'prueba',
            'email' => 'test@mail.com',
            'password' => Hash::make('test1234')
        ]);

        $this->call(PersonaSeeder::class);
    }
}
