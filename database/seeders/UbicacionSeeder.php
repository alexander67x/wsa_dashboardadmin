<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ubicaciones integradas en las tablas proyectos y almacenes.
        // Este seeder queda como no-op para evitar errores si la tabla ubicaciones ya no existe.
        return;
    }
}
