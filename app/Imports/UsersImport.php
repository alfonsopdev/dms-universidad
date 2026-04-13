<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class UsersImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private int   $importedCount = 0;
    // 1. Cambiamos el nombre para evitar el conflicto con SkipsErrors
    private array $importErrors = []; 

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                // Validar fila
                if (empty($row['name']) || empty($row['email'])) {
                    $this->importErrors[] = "Fila " . ($index + 2) . ": nombre y correo son requeridos.";
                    continue;
                }

                // Verificar si ya existe
                if (User::where('email', $row['email'])->exists()) {
                    $this->importErrors[] = "Fila " . ($index + 2) . ": {$row['email']} ya está registrado.";
                    continue;
                }

                $user = User::create([
                    'name'      => trim($row['name']),
                    'email'     => strtolower(trim($row['email'])),
                    'password'  => Hash::make($row['password'] ?? str()->random(12)),
                    'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
                ]);

                // Asignar rol
                $role = trim($row['role'] ?? 'usuario');
                if (\Spatie\Permission\Models\Role::where('name', $role)->exists()) {
                    $user->assignRole($role);
                } else {
                    $user->assignRole('usuario');
                }

                $this->importedCount++;

            } catch (\Exception $e) {
                // 2. Usamos el nuevo nombre de la variable
                $this->importErrors[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function getImportedCount(): int  { return $this->importedCount; }
    // 3. Devolvemos la variable con el nuevo nombre
    public function getErrors(): array       { return $this->importErrors; } 
}