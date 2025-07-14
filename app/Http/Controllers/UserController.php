<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Cargar carteras asignadas a cada usuario para el multiselect
        $users = User::with(['reportes.cartera'])->get();
        $carteras = Cartera::with('reportes')->get();
        // Agregar carteras asignadas a cada usuario (solo objetos completos)
        foreach ($users as $user) {
            $user->carteras = $carteras->filter(function ($c) use ($user) {
                return $user->reportes->contains(function ($r) use ($c) {
                    return $r->cartera_id === $c->id;
                });
            })->values();
        }
        return Inertia::render('GestionarUsuarios', [
            'users' => $users,
            'reportes' => Reporte::with('cartera')->get(),
            'carteras' => $carteras,
            'success' => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'active' => 'required|boolean',
            'reportes' => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
        ]);
        // Limpiar carteras si viene del frontend
        unset($validated['carteras']);

        $user = new User();
        $this->saveUser($user, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'data' => $user->load('reportes.cartera'),
            ], 201);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'active' => 'required|boolean',
            'reportes' => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
        ]);
        unset($validated['carteras']);

        $this->saveUser($user, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data' => $user->load('reportes.cartera'),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.'
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function saveUser(User $user, array $data): void
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->active = $data['active'];

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        $user->reportes()->sync($data['reportes'] ?? []);
    }

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            // Redirige o muestra error si no está autenticado
            return redirect()->route('login');
        }

        // Mapear reportes del usuario
        $reportes = $user->reportes ? $user->reportes->map(function ($reporte) {
            return [
                'id' => $reporte->id,
                'nombre' => $reporte->nombre,
                'src' => $reporte->link ?? '',
                'cartera' => $reporte->cartera,
            ];
        }) : collect();

        // Obtener carteras únicas de los reportes
        $carteras = $user->reportes ? $user->reportes
            ->filter(fn($r) => $r->cartera)
            ->pluck('cartera')
            ->unique('id')
            ->values() : collect();

        return Inertia::render('Dashboard', [
            'reportes' => $reportes,
            'carteras' => $carteras,
        ]);
    }
}
