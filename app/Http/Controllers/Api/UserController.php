<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class UserController extends Controller
{
    /**
     * Listar usuarios con roles
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'unit'])
            ->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name',  'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('dni',   'like', "%{$request->search}%");
            });
        }
        if ($request->role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    /**
     * Crear usuario individual
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password ?? str()->random(12)),
            'dni'       => $request->dni,
            'phone'     => $request->phone,
            'position'  => $request->position,
            'unit_id'   => $request->unit_id,
            'is_active' => $request->is_active ?? true,
        ]);

        if ($request->role) {
            $user->assignRole($request->role);
        }

        return response()->json($user->load('roles', 'unit'), 201);
    }

    /**
     * Ver detalle de usuario
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('roles', 'permissions'));
    }

    /**
     * Actualizar usuario
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->only([
            'name', 'email', 'dni', 'phone',
            'position', 'unit_id', 'is_active'
        ]);

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json($user->load('roles', 'unit'));
    }

    /**
     * Desactivar usuario (no eliminar)
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'No puedes desactivarte a ti mismo.'], 403);
        }

        $user->update(['is_active' => false]);
        return response()->json(['message' => 'Usuario desactivado.']);
    }

    /**
     * Listar roles disponibles
     */
    public function roles(): JsonResponse
    {
        return response()->json(Role::orderBy('name')->get());
    }

    /**
     * Importar usuarios masivamente desde Excel
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'message'  => "Importación completada.",
                'imported' => $import->getImportedCount(),
                'errors'   => $import->getErrors(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al procesar el archivo: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Descargar plantilla Excel
     */
    public function template(): mixed
    {
        $headers = ['name','email','password','dni','phone','position','unit_id','role','is_active'];
        $example = ['Juan Pérez','juan@universidad.edu.pe','password123','12345678','999999999','Jefe de Área','1','usuario','1'];

        $content = implode(',', $headers) . "\n" . implode(',', $example) . "\n";

        return response($content, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_usuarios.csv"',
        ]);
    }
}