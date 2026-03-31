<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\BajaGarantiaMaterial;
use App\Models\ReclamoGarantiaHistorial;
use App\Models\ReclamoGarantiaMaterial;
use App\Models\StockAlmacen;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class GarantiaClaimService
{
    public function crearReclamo(
        int $stockId,
        float $cantidadReclamada,
        string $motivoDano,
        ?string $observaciones,
        int $creadoPor
    ): ReclamoGarantiaMaterial {
        if ($cantidadReclamada <= 0) {
            throw new InvalidArgumentException('La cantidad reclamada debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($stockId, $cantidadReclamada, $motivoDano, $observaciones, $creadoPor) {
            /** @var StockAlmacen|null $stock */
            $stock = StockAlmacen::query()
                ->whereKey($stockId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new RuntimeException('No se encontró el registro de stock.');
            }

            if (! $this->hasActiveWarranty($stock)) {
                throw new RuntimeException('El stock seleccionado no tiene garantía activa.');
            }

            $stockReal = max(0, (float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada);
            if ($stockReal < $cantidadReclamada) {
                throw new RuntimeException("Stock real insuficiente. Disponible: {$stockReal}");
            }

            $almacen = Almacen::query()->find($stock->id_almacen);

            $reclamo = ReclamoGarantiaMaterial::create([
                'id_stock' => $stock->id,
                'id_material' => $stock->id_material,
                'id_almacen' => $stock->id_almacen,
                'id_lote' => $stock->id_lote,
                'cod_proy' => $almacen?->cod_proy,
                'cantidad_reclamada' => $cantidadReclamada,
                'estado' => 'abierto',
                'motivo_dano' => trim($motivoDano),
                'observaciones' => $observaciones ? trim($observaciones) : null,
                'creado_por' => $creadoPor,
            ]);

            $stock->increment('cantidad_reservada', $cantidadReclamada);

            $this->registrarMovimientoInventario(
                tipoMovimiento: 'reserva',
                stock: $stock,
                cantidad: $cantidadReclamada,
                referencia: 'RECLAMO-'.$reclamo->id_reclamo,
                motivo: "Reserva por reclamo de garantía #{$reclamo->id_reclamo}",
                registradoPor: $creadoPor,
            );

            $this->registrarHistorial(
                reclamo: $reclamo,
                estadoAnterior: null,
                estadoNuevo: 'abierto',
                accion: 'Creación de reclamo',
                comentario: $observaciones,
                creadoPor: $creadoPor,
            );

            return $reclamo->fresh();
        });
    }

    public function aprobarReclamo(int $reclamoId, int $aprobadoPor, ?string $comentario = null): ReclamoGarantiaMaterial
    {
        return DB::transaction(function () use ($reclamoId, $aprobadoPor, $comentario) {
            /** @var ReclamoGarantiaMaterial|null $reclamo */
            $reclamo = ReclamoGarantiaMaterial::query()
                ->whereKey($reclamoId)
                ->lockForUpdate()
                ->first();

            if (! $reclamo) {
                throw new RuntimeException('No se encontró el reclamo de garantía.');
            }

            if ($reclamo->estado !== 'abierto') {
                throw new RuntimeException('Solo se pueden aprobar reclamos en estado abierto.');
            }

            $estadoAnterior = $reclamo->estado;

            $reclamo->update([
                'estado' => 'aprobado',
                'aprobado_por' => $aprobadoPor,
                'fecha_aprobacion' => now(),
            ]);

            $this->registrarHistorial(
                reclamo: $reclamo,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: 'aprobado',
                accion: 'Aprobación de reclamo',
                comentario: $comentario,
                creadoPor: $aprobadoPor,
            );

            return $reclamo->fresh();
        });
    }

    public function rechazarReclamo(int $reclamoId, int $rechazadoPor, ?string $comentario = null): ReclamoGarantiaMaterial
    {
        return DB::transaction(function () use ($reclamoId, $rechazadoPor, $comentario) {
            /** @var ReclamoGarantiaMaterial|null $reclamo */
            $reclamo = ReclamoGarantiaMaterial::query()
                ->whereKey($reclamoId)
                ->lockForUpdate()
                ->first();

            if (! $reclamo) {
                throw new RuntimeException('No se encontró el reclamo de garantía.');
            }

            if ($reclamo->estado !== 'abierto') {
                throw new RuntimeException('Solo se pueden rechazar reclamos en estado abierto.');
            }

            /** @var StockAlmacen|null $stock */
            $stock = StockAlmacen::query()
                ->whereKey($reclamo->id_stock)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new RuntimeException('No se encontró el stock asociado al reclamo.');
            }

            $reservadaActual = (float) $stock->cantidad_reservada;
            $disponibleActual = (float) $stock->cantidad_disponible;
            $cantidadReclamada = (float) $reclamo->cantidad_reclamada;
            if ($reservadaActual < $cantidadReclamada) {
                throw new RuntimeException('Inconsistencia de stock reservado para este reclamo.');
            }

            if ($disponibleActual < $cantidadReclamada) {
                throw new RuntimeException('No se puede rechazar como baja definitiva: stock disponible insuficiente.');
            }

            $stock->decrement('cantidad_reservada', $cantidadReclamada);
            $stock->decrement('cantidad_disponible', $cantidadReclamada);

            $estadoAnterior = $reclamo->estado;
            $reclamo->update([
                'estado' => 'rechazado',
                'resultado_final' => 'baja_definitiva',
                'finalizado_por' => $rechazadoPor,
                'fecha_finalizacion' => now(),
            ]);

            $this->registrarMovimientoInventario(
                tipoMovimiento: 'ajuste',
                stock: $stock,
                cantidad: $cantidadReclamada,
                referencia: 'RECLAMO-'.$reclamo->id_reclamo,
                motivo: "Baja definitiva por rechazo de reclamo #{$reclamo->id_reclamo}",
                registradoPor: $rechazadoPor,
            );

            BajaGarantiaMaterial::updateOrCreate(
                ['id_reclamo' => $reclamo->id_reclamo],
                [
                    'id_stock' => $stock->id,
                    'id_material' => $stock->id_material,
                    'id_almacen' => $stock->id_almacen,
                    'id_lote' => $stock->id_lote,
                    'cod_proy' => $reclamo->cod_proy,
                    'cantidad_baja' => $cantidadReclamada,
                    'motivo' => $reclamo->motivo_dano,
                    'observaciones' => $comentario,
                    'registrado_por' => $rechazadoPor,
                    'fecha_baja' => now(),
                ],
            );

            $this->registrarHistorial(
                reclamo: $reclamo,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: 'rechazado',
                accion: 'Rechazo de reclamo',
                comentario: $comentario,
                creadoPor: $rechazadoPor,
            );

            return $reclamo->fresh();
        });
    }

    public function finalizarReclamo(
        int $reclamoId,
        int $finalizadoPor,
        string $resultadoFinal,
        ?string $comentario = null
    ): ReclamoGarantiaMaterial {
        if (! in_array($resultadoFinal, ['devuelto_stock', 'baja_definitiva'], true)) {
            throw new InvalidArgumentException('El resultado final es inválido.');
        }

        return DB::transaction(function () use ($reclamoId, $finalizadoPor, $resultadoFinal, $comentario) {
            /** @var ReclamoGarantiaMaterial|null $reclamo */
            $reclamo = ReclamoGarantiaMaterial::query()
                ->whereKey($reclamoId)
                ->lockForUpdate()
                ->first();

            if (! $reclamo) {
                throw new RuntimeException('No se encontró el reclamo de garantía.');
            }

            if ($reclamo->estado !== 'aprobado') {
                throw new RuntimeException('Solo se puede finalizar un reclamo aprobado.');
            }

            /** @var StockAlmacen|null $stock */
            $stock = StockAlmacen::query()
                ->whereKey($reclamo->id_stock)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new RuntimeException('No se encontró el stock asociado al reclamo.');
            }

            $cantidadReclamada = (float) $reclamo->cantidad_reclamada;
            $reservadaActual = (float) $stock->cantidad_reservada;
            $disponibleActual = (float) $stock->cantidad_disponible;

            if ($reservadaActual < $cantidadReclamada) {
                throw new RuntimeException('Inconsistencia: cantidad reservada menor al reclamo.');
            }

            if ($resultadoFinal === 'baja_definitiva' && $disponibleActual < $cantidadReclamada) {
                throw new RuntimeException('No se puede dar de baja: stock disponible insuficiente.');
            }

            $stock->decrement('cantidad_reservada', $cantidadReclamada);

            if ($resultadoFinal === 'baja_definitiva') {
                $stock->decrement('cantidad_disponible', $cantidadReclamada);
                $this->registrarMovimientoInventario(
                    tipoMovimiento: 'ajuste',
                    stock: $stock,
                    cantidad: $cantidadReclamada,
                    referencia: 'RECLAMO-'.$reclamo->id_reclamo,
                    motivo: "Baja definitiva por reclamo de garantía #{$reclamo->id_reclamo}",
                    registradoPor: $finalizadoPor,
                );

                BajaGarantiaMaterial::updateOrCreate(
                    ['id_reclamo' => $reclamo->id_reclamo],
                    [
                        'id_stock' => $stock->id,
                        'id_material' => $stock->id_material,
                        'id_almacen' => $stock->id_almacen,
                        'id_lote' => $stock->id_lote,
                        'cod_proy' => $reclamo->cod_proy,
                        'cantidad_baja' => $cantidadReclamada,
                        'motivo' => $reclamo->motivo_dano,
                        'observaciones' => $comentario,
                        'registrado_por' => $finalizadoPor,
                        'fecha_baja' => now(),
                    ],
                );
            } else {
                $this->registrarMovimientoInventario(
                    tipoMovimiento: 'liberacion',
                    stock: $stock,
                    cantidad: $cantidadReclamada,
                    referencia: 'RECLAMO-'.$reclamo->id_reclamo,
                    motivo: "Liberación por cierre de reclamo #{$reclamo->id_reclamo}",
                    registradoPor: $finalizadoPor,
                );
            }

            $estadoAnterior = $reclamo->estado;

            $reclamo->update([
                'estado' => 'finalizado',
                'resultado_final' => $resultadoFinal,
                'finalizado_por' => $finalizadoPor,
                'fecha_finalizacion' => now(),
            ]);

            $this->registrarHistorial(
                reclamo: $reclamo,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: 'finalizado',
                accion: 'Finalización de reclamo',
                comentario: $comentario,
                creadoPor: $finalizadoPor,
            );

            return $reclamo->fresh();
        });
    }

    private function hasActiveWarranty(StockAlmacen $stock): bool
    {
        if (! $stock->garantia_dias || $stock->garantia_dias <= 0 || ! $stock->created_at) {
            return false;
        }

        $warrantyEnd = $stock->created_at->copy()->addDays((int) $stock->garantia_dias)->endOfDay();

        return Carbon::now()->lte($warrantyEnd);
    }

    private function registrarHistorial(
        ReclamoGarantiaMaterial $reclamo,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $accion,
        ?string $comentario,
        ?int $creadoPor
    ): void {
        ReclamoGarantiaHistorial::create([
            'id_reclamo' => $reclamo->id_reclamo,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'accion' => $accion,
            'comentario' => $comentario,
            'creado_por' => $creadoPor,
            'created_at' => now(),
        ]);
    }

    private function registrarMovimientoInventario(
        string $tipoMovimiento,
        StockAlmacen $stock,
        float $cantidad,
        string $referencia,
        string $motivo,
        int $registradoPor
    ): void {
        DB::table('movimientos_inventario')->insert([
            'numero_movimiento' => $this->generateMovementNumber(),
            'id_material' => $stock->id_material,
            'id_lote' => $stock->id_lote,
            'id_almacen_origen' => $stock->id_almacen,
            'id_almacen_destino' => null,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad' => $cantidad,
            'referencia' => $referencia,
            'motivo' => $motivo,
            'fecha_movimiento' => now(),
            'registrado_por' => $registradoPor,
            'created_at' => now(),
        ]);
    }

    private function generateMovementNumber(): string
    {
        return 'MOV-GAR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
