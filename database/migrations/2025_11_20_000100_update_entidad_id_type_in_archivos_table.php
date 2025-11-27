<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE archivos MODIFY entidad_id VARCHAR(191)');
    }

    public function down(): void
    {
        // Clean non numeric values before converting back to integer
        DB::table('archivos')
            ->where(function ($query) {
                $query->whereNull('entidad_id')
                    ->orWhere('entidad_id', '')
                    ->orWhereRaw("entidad_id REGEXP '^[0-9]+$' = 0");
            })
            ->update(['entidad_id' => null]);

        DB::statement('ALTER TABLE archivos MODIFY entidad_id INT');
    }
};
