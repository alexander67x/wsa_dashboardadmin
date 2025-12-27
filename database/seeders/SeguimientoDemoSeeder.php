<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Proyecto;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SeguimientoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Cliente::first();
        $responsable = Empleado::orderBy('cod_empleado')->first();
        $supervisor = Empleado::where('cargo', 'Supervisor de Instalaciones')->first() ?? $responsable;

        if (! $cliente || ! $responsable) {
            $this->command?->warn('No se encontró cliente o empleado para crear el proyecto demo de seguimiento.');
            return;
        }

        Proyecto::updateOrCreate(
            ['cod_proy' => 'SEG-HIDRO-004'],
            [
                'cod_cliente' => $cliente->getKey(),
                'nombre_ubicacion' => 'Complejo Hidroeléctrico Valles Unidos',
                'direccion' => 'Carretera Potosí - Tarija km 85',
                'ciudad' => 'Potosí',
                'pais' => 'Bolivia',
                'latitud' => -19.5833333,
                'longitud' => -65.7500000,
                'tipo_ubicacion' => 'obra',
                'fecha_inicio' => Carbon::now()->startOfYear()->addWeeks(8),
                'fecha_fin_estimada' => Carbon::now()->startOfYear()->addWeeks(20),
                'estado' => 'activo',
                'descripcion' => 'Proyecto demo para la curva de seguimiento con avances semanales completos.',
                'avance_financiero' => 0,
                'gasto_real' => 0,
                'rentabilidad' => 25,
                'responsable_proyecto' => $responsable->getKey(),
                'supervisor_obra' => $supervisor->getKey(),
            ],
        );

        $this->command?->info('✔ Proyecto demo SEG-HIDRO-004 listo para la curva de seguimiento.');
    }
}
