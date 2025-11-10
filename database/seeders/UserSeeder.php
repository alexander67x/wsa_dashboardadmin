<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'email' => 'admin@admin.com',
                'name' => 'Administrador',
                'password' => 'admin123',
            ],
            [
                'email' => 'gerente@empresa.com',
                'name' => 'Gerente General',
                'password' => 'gerente123',
            ],
            [
                'email' => 'supervisor@empresa.com',
                'name' => 'Supervisor de Obra',
                'password' => 'supervisor123',
            ],
            [
                'email' => 'almacen@empresa.com',
                'name' => 'Responsable de Almacén',
                'password' => 'almacen123',
            ],
            [
                'email' => 'proyectos@empresa.com',
                'name' => 'Gestor de Proyectos',
                'password' => 'proyectos123',
            ],
            [
                'email' => 'test@test.com',
                'name' => 'Usuario de Prueba',
                'password' => 'test123',
            ],
        ];

        $this->command->info("🔐 Creando usuarios del sistema...");
        $this->command->newLine();

        foreach ($usuarios as $usuarioData) {
            $usuario = User::firstOrCreate(
                ['email' => $usuarioData['email']],
                [
                    'name' => $usuarioData['name'],
                    'password' => Hash::make($usuarioData['password']),
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            $this->command->info("✅ {$usuario->name}:");
            $this->command->info("   Email: {$usuario->email}");
            $this->command->info("   Contraseña: {$usuarioData['password']}");
        }

        $this->command->newLine();
        $totalUsers = User::count();
        $this->command->info("📊 Total de usuarios en el sistema: {$totalUsers}");
    }
}
