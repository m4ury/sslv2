<?php

namespace App\Http\Controllers;

use App\Models\Profesion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Usuarios', [
            'usuarios' => User::with('profesion:id,nombre')->orderBy('name')->get(),
            'profesiones' => Profesion::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'profesion_id' => ['required', Rule::exists('profesiones', 'id')],
            'password' => ['required', Password::defaults()],
        ]);

        $usuario = new User($data);
        // Lo crea un administrador: se marca verificado para que pueda ingresar
        $usuario->email_verified_at = now();
        $usuario->save();

        return back()->with('success', 'Usuario creado.');
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario)],
            'profesion_id' => ['required', Rule::exists('profesiones', 'id')],
            'activo' => ['required', 'boolean'],
            'password_nueva' => ['nullable', Password::defaults()],
        ]);

        $seraActivo = (bool) $data['activo'];

        if ($usuario->is($request->user()) && ! $seraActivo) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        // Siempre debe quedar al menos un administrador activo
        $seraAdmin = $seraActivo
            && Profesion::whereKey($data['profesion_id'])->where('es_admin', true)->exists();

        if ($usuario->esAdmin() && ! $seraAdmin && ! $this->hayOtroAdmin($usuario)) {
            return back()->with('error', 'Debe quedar al menos un administrador activo.');
        }

        $usuario->fill(Arr::only($data, ['name', 'email', 'profesion_id', 'activo']));

        if (! empty($data['password_nueva'])) {
            $usuario->password = $data['password_nueva'];
        }

        $usuario->save();

        return back()->with('success', 'Usuario actualizado.');
    }

    private function hayOtroAdmin(User $excluir): bool
    {
        return User::where('activo', true)
            ->whereKeyNot($excluir->id)
            ->whereHas('profesion', fn ($q) => $q->where('es_admin', true))
            ->exists();
    }
}
