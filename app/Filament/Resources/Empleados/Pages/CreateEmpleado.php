<?php

namespace App\Filament\Resources\Empleados\Pages;

use App\Filament\Resources\Empleados\EmpleadoResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmpleado extends CreateRecord
{
    protected static string $resource = EmpleadoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Obtener el password del formulario (usar getRawState porque el campo está dehydrated)
        $formData = $this->form->getRawState();
        $password = $formData['password'] ?? null;

        if (!$password) {
            throw new \Exception('La contraseña es requerida');
        }

        // Crear el usuario asociado
        $user = User::create([
            'name' => $data['nombre_completo'],
            'email' => $data['email'],
            'password' => Hash::make($password),
        ]);

        // Asignar el user_id al empleado
        $data['user_id'] = $user->id;

        return $data;
    }
}
