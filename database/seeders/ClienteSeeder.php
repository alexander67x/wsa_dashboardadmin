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
            ['email' => 'juan.perez@constructoraabc.com'],
            [
                'nombre_cliente' => 'Constructora ABC S.A.',
                'industria' => 'Construcción',
                'contacto_principal' => 'Juan Pérez',
                'telefono' => '+591-2-1234567',
                'direccion' => 'Av. Principal #123, La Paz',
                'activo' => true,
            ]
        );

        $cliente2 = Cliente::firstOrCreate(
            ['email' => 'maria.gonzalez@inmobiliariaxyz.com'],
            [
                'nombre_cliente' => 'Inmobiliaria XYZ Ltda.',
                'industria' => 'Inmobiliaria',
                'contacto_principal' => 'María González',
                'telefono' => '+591-3-2345678',
                'direccion' => 'Calle Comercio #456, Santa Cruz',
                'activo' => true,
            ]
        );

        $cliente3 = Cliente::firstOrCreate(
            ['email' => 'carlos.rodriguez@desarrollosurbanos.com'],
            [
                'nombre_cliente' => 'Desarrollos Urbanos SRL',
                'industria' => 'Desarrollo Urbano',
                'contacto_principal' => 'Carlos Rodríguez',
                'telefono' => '+591-4-3456789',
                'direccion' => 'Plaza Principal #789, Cochabamba',
                'activo' => true,
            ]
        );

        $this->command->info("✅ Clientes creados/verificados: {$cliente1->nombre_cliente}, {$cliente2->nombre_cliente}, {$cliente3->nombre_cliente}");
    }
}
