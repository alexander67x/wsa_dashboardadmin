<?php

namespace App\Filament\Resources\Empleados\Pages;

use App\Filament\Resources\Empleados\EmpleadoResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditEmpleado extends EditRecord
{
    protected static string $resource = EmpleadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $empleado = $this->record;
        
        // Obtener el password del formulario (usar getRawState porque el campo está dehydrated)
        $formData = $this->form->getRawState();
        $password = $formData['password'] ?? null;
        
        // Si no tiene usuario asociado, crear uno
        if (!$empleado->user_id) {
            $user = User::create([
                'name' => $data['nombre_completo'],
                'email' => $data['email'],
                'password' => Hash::make($password ?? 'password123'), // Contraseña por defecto si no se proporciona
            ]);
            $data['user_id'] = $user->id;
        } else {
            // Actualizar el usuario existente
            $user = User::find($empleado->user_id);
            if ($user) {
                $user->update([
                    'name' => $data['nombre_completo'],
                    'email' => $data['email'],
                ]);
                
                // Actualizar contraseña solo si se proporciona una nueva
                if (!empty($password)) {
                    $user->update([
                        'password' => Hash::make($password),
                    ]);
                }
            }
        }

        return $data;
    }
}
