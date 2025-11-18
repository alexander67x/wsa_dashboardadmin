<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cliente1 = Cliente::firstOrCreate(
            ['email' => 'luis.herrera@bancouniversal.com'],
            [
                'nombre_cliente' => 'Banco Universal S.A.',
                'industria' => 'Banca y finanzas',
                'contacto_principal' => 'Luis Herrera',
                'telefono' => '+591-2-1234567',
                'direccion' => 'Av. Mariscal Santa Cruz #100, La Paz',
                'activo' => true,
            ]
        );

        $cliente2 = Cliente::firstOrCreate(
            ['email' => 'carla.rojas@retailmax.com'],
            [
                'nombre_cliente' => 'RetailMax Supermercados',
                'industria' => 'Retail y supermercados',
                'contacto_principal' => 'Carla Rojas',
                'telefono' => '+591-3-2345678',
                'direccion' => 'Av. Cristo Redentor #456, Santa Cruz',
                'activo' => true,
            ]
        );

        $cliente3 = Cliente::firstOrCreate(
            ['email' => 'javier.flores@clinicasanmarcos.com'],
            [
                'nombre_cliente' => 'Clínica San Marcos',
                'industria' => 'Salud',
                'contacto_principal' => 'Javier Flores',
                'telefono' => '+591-4-3456789',
                'direccion' => 'Av. Blanco Galindo #789, Cochabamba',
                'activo' => true,
            ]
        );

        $this->command->info("✔ Clientes creados/verificados: {$cliente1->nombre_cliente}, {$cliente2->nombre_cliente}, {$cliente3->nombre_cliente}");
    }
}

