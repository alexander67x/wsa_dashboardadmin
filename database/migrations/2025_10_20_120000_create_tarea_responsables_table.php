<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea_responsables', function (Blueprint $table) {
            $table->id();
            $table->integer('tarea_id');
            $table->integer('responsable_id');
            $table->timestamps();

            $table->unique(['tarea_id', 'responsable_id']);
            $table->foreign('tarea_id')->references('id_tarea')->on('tareas')->cascadeOnDelete();
            $table->foreign('responsable_id')->references('cod_empleado')->on('empleados')->cascadeOnDelete();
        });

        if (Schema::hasColumn('tareas', 'responsable_id')) {
            DB::table('tareas')
                ->whereNotNull('responsable_id')
                ->whereRaw("TRIM(responsable_id) REGEXP '^[0-9]+$'")
                ->orderBy('id_tarea')
                ->chunk(200, function ($rows) {
                    $now = now();
                    $payload = [];

                    foreach ($rows as $row) {
                        $rawId = trim((string) $row->responsable_id);
                        $responsableId = is_numeric($rawId) ? (int) $rawId : null;

                        if (! $responsableId) {
                            continue;
                        }

                        $payload[] = [
                            'tarea_id' => $row->id_tarea,
                            'responsable_id' => $responsableId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if (! empty($payload)) {
                        DB::table('tarea_responsables')->insert($payload);
                    }
                });

            Schema::table('tareas', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_tareas_responsable_id');
                } catch (\Throwable $e) {
                    // Foreign key may not exist in some environments.
                }

                try {
                    $table->dropIndex('idx_tareas_cod_proy_responsable');
                } catch (\Throwable $e) {
                    // Index may not exist if migrations were refreshed.
                }

                $table->dropColumn('responsable_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tareas', 'responsable_id')) {
            Schema::table('tareas', function (Blueprint $table) {
                $table->integer('responsable_id')->nullable()->index()->after('estado');
            });

            Schema::table('tareas', function (Blueprint $table) {
                $table->index(['cod_proy', 'responsable_id'], 'idx_tareas_cod_proy_responsable');
                $table->foreign('responsable_id', 'fk_tareas_responsable_id')
                    ->references('cod_empleado')
                    ->on('empleados')
                    ->onUpdate('no action')
                    ->onDelete('restrict');
            });
        }

        if (Schema::hasTable('tarea_responsables')) {
            $assignments = DB::table('tarea_responsables')
                ->select('tarea_id', DB::raw('MIN(responsable_id) as responsable_id'))
                ->groupBy('tarea_id')
                ->get();

            foreach ($assignments as $assignment) {
                DB::table('tareas')
                    ->where('id_tarea', $assignment->tarea_id)
                    ->update(['responsable_id' => $assignment->responsable_id]);
            }

            Schema::dropIfExists('tarea_responsables');
        }
    }
};
