<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden
        $this->call([
            RoleSeeder::class,              // Roles primero
            UserSeeder::class,              // Usuarios
            ClienteSeeder::class,           // Clientes
            EmpleadoSeeder::class,          // Empleados
            ProyectoSeeder::class,          // Proyectos
            AlmacenSeeder::class,           // Almacenes
            MaterialGrupoSeeder::class,     // Grupos de materiales
            MaterialSubgrupoSeeder::class,  // Subgrupos de materiales
            MaterialSeeder::class,          // Materiales
            TareaSeeder::class,             // Tareas
            ReporteSeeder::class,           // Reportes (después de tareas)
        ]);
    }
}
