<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GarantiaClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GarantiaClaimController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $empleado = $this->resolveAuthorizedEmpleado($request);

        $data = $request->validate([
            'stockId' => ['required', 'integer', 'exists:stock_almacen,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'damageReason' => ['required', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $reclamo = app(GarantiaClaimService::class)->crearReclamo(
                stockId: (int) $data['stockId'],
                cantidadReclamada: (float) $data['quantity'],
                motivoDano: (string) $data['damageReason'],
                observaciones: $data['observations'] ?? null,
                creadoPor: (int) $empleado->cod_empleado,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => (string) $reclamo->id_reclamo,
            'estado' => $reclamo->estado,
            'message' => 'Reclamo de garantía creado correctamente.',
        ], 201);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $empleado = $this->resolveAuthorizedEmpleado($request);
        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reclamo = app(GarantiaClaimService::class)->aprobarReclamo(
                reclamoId: $id,
                aprobadoPor: (int) $empleado->cod_empleado,
                comentario: $data['comment'] ?? null,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => (string) $reclamo->id_reclamo,
            'estado' => $reclamo->estado,
            'message' => 'Reclamo aprobado.',
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $empleado = $this->resolveAuthorizedEmpleado($request);
        $data = $request->validate([
            'comment' => ['required', 'string', 'max:500'],
        ]);

        try {
            $reclamo = app(GarantiaClaimService::class)->rechazarReclamo(
                reclamoId: $id,
                rechazadoPor: (int) $empleado->cod_empleado,
                comentario: $data['comment'],
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => (string) $reclamo->id_reclamo,
            'estado' => $reclamo->estado,
            'message' => 'Reclamo rechazado.',
        ]);
    }

    public function finalize(Request $request, int $id): JsonResponse
    {
        $empleado = $this->resolveAuthorizedEmpleado($request);
        $data = $request->validate([
            'result' => ['required', 'string', 'in:devuelto_stock,baja_definitiva'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reclamo = app(GarantiaClaimService::class)->finalizarReclamo(
                reclamoId: $id,
                finalizadoPor: (int) $empleado->cod_empleado,
                resultadoFinal: (string) $data['result'],
                comentario: $data['comment'] ?? null,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => (string) $reclamo->id_reclamo,
            'estado' => $reclamo->estado,
            'resultadoFinal' => $reclamo->resultado_final,
            'message' => 'Reclamo finalizado.',
        ]);
    }

    private function resolveAuthorizedEmpleado(Request $request)
    {
        $user = $request->user();
        $empleado = $user?->empleado;

        if (! $empleado) {
            abort(403, 'No se encontró un empleado asociado al usuario.');
        }

        if (! in_array($empleado->role?->slug, ['adquisiciones', 'gerencia'], true)) {
            abort(403, 'No tienes permisos para gestionar reclamos de garantía.');
        }

        return $empleado;
    }
}
