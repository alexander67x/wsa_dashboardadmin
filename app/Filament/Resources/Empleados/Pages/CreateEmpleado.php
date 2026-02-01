<?php

namespace App\Filament\Resources\Empleados\Pages;

use App\Filament\Resources\Empleados\EmpleadoResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
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

        if (User::where('email', $data['email'])->exists()) {
            Notification::make()
                ->title('El correo ya está registrado en otro usuario.')
                ->danger()
                ->send();

            throw new Halt();
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
