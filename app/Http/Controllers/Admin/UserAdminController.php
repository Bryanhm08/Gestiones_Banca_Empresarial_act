<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::with('area:id,nombre')
            ->when($q !== '', function ($qry) use ($q) {
                $like = "%{$q}%";
                $qry->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('username', 'like', $like);
                });
            })
            ->latest('id')
            ->get([
                'id','username','name','email','area_id','puesto','asesor','admin','estado','created_at'
            ])
            ->map(fn($u) => [
                'id'       => $u->id,
                'username' => $u->username,
                'name'     => $u->name,
                'email'    => $u->email,
                'puesto'   => $u->puesto,
                'area'     => $u->area?->nombre,
                'area_id'  => $u->area_id,
                'asesor'   => (bool) $u->asesor,
                'admin'    => (bool) $u->admin,
                'estado'   => (bool) $u->estado,
                'created'  => optional($u->created_at)->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'areas' => Area::orderBy('nombre')->get(['id','nombre']),
            'query' => $q,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // Hashear contraseña
        $data['password'] = Hash::make($data['password'] ?? '');

        // Limpiar campos que no son columnas
        unset($data['password_confirmation']);

        User::create($data);

        return back()->with('success', 'Usuario creado');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Si el admin envía una nueva contraseña, se cambia; si no, se deja igual
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Limpiar confirmación
        unset($data['password_confirmation']);

        $user->update($data);

        return back()->with('success', 'Usuario actualizado');
    }

    public function toggleEstado(User $user)
    {
        $user->estado = !$user->estado;
        $user->save();

        return response()->json(['ok' => true, 'estado' => $user->estado]);
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'asesor' => ['required','boolean'],
            'admin'  => ['required','boolean'],
        ]);

        $user->asesor = $request->boolean('asesor');
        $user->admin  = $request->boolean('admin');
        $user->save();

        return response()->json([
            'ok'     => true,
            'asesor' => $user->asesor,
            'admin'  => $user->admin,
        ]);
    }
}
