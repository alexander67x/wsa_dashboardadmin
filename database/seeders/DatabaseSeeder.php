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
            UserSeeder::class,              // Usuarios primero
            ClienteSeeder::class,           // Clientes
            EmpleadoSeeder::class,          // Empleados
            ProyectoSeeder::class,          // Proyectos
            AlmacenSeeder::class,           // Almacenes
            MaterialGrupoSeeder::class,     // Grupos de materiales
            MaterialSubgrupoSeeder::class,  // Subgrupos de materiales
            MaterialSeeder::class,          // Materiales
            TareaSeeder::class,             // Tareas
        ]);
    }
}
