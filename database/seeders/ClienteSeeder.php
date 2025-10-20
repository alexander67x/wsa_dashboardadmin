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
        Cliente::create([
            'nombre_cliente' => 'Constructora ABC S.A.',
            'industria' => 'Construcción',
            'contacto_principal' => 'Juan Pérez',
            'email' => 'juan.perez@constructoraabc.com',
            'telefono' => '+591-2-1234567',
            'direccion' => 'Av. Principal #123, La Paz',
            'activo' => true,
        ]);

        Cliente::create([
            'nombre_cliente' => 'Inmobiliaria XYZ Ltda.',
            'industria' => 'Inmobiliaria',
            'contacto_principal' => 'María González',
            'email' => 'maria.gonzalez@inmobiliariaxyz.com',
            'telefono' => '+591-3-2345678',
            'direccion' => 'Calle Comercio #456, Santa Cruz',
            'activo' => true,
        ]);

        Cliente::create([
            'nombre_cliente' => 'Desarrollos Urbanos SRL',
            'industria' => 'Desarrollo Urbano',
            'contacto_principal' => 'Carlos Rodríguez',
            'email' => 'carlos.rodriguez@desarrollosurbanos.com',
            'telefono' => '+591-4-3456789',
            'direccion' => 'Plaza Principal #789, Cochabamba',
            'activo' => true,
        ]);
    }
}
