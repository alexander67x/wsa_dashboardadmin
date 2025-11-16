<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\SolicitudMaterial;
use App\Models\SolicitudItem;
use App\Models\Empleado;
use App\Models\MaterialDelivery;
use App\Models\SolicitudHistorial;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
	public function catalog(Request $request)
	{
		$validated = $request->validate([
			'projectId' => ['required', 'string', 'exists:proyectos,cod_proy'],
		]);

		// Buscar almacén asociado al proyecto
		$almacen = \App\Models\Almacen::where('cod_proy', $validated['projectId'])
			->where('activo', true)
			->first();

		if (!$almacen) {
			return response()->json([
				'message' => 'No se encontró un almacén activo para este proyecto'
			], 404);
		}

		// Obtener solo los materiales que están en el almacén del proyecto
		return \App\Models\StockAlmacen::with('material')
			->where('id_almacen', $almacen->id_almacen)
			->whereHas('material', function ($query) {
				$query->where('activo', true);
			})
			->get()
			->map(function ($stock) {
				$material = $stock->material;
				return [
					'id' => (string) $material->id_material,
					'name' => $material->nombre_producto,
					'sku' => $material->codigo_producto,
					'unit' => $material->unidad_medida,
				];
			})
			->values();
	}

	public function index(Request $request)
	{
		$user = $request->user();
		$empleado = $user->empleado;
		
		if (!$empleado) {
			return response()->json(['message' => 'No se encontró un empleado asociado al usuario'], 404);
		}

		$query = SolicitudMaterial::with(['proyecto', 'solicitadoPor', 'items.material'])
			->orderBy('fecha_solicitud', 'desc');

		// Filtrar por proyecto si se proporciona
		if ($request->has('projectId')) {
			$query->where('cod_proy', $request->projectId);
		}

		// Filtrar por estado si se proporciona
		if ($request->has('estado')) {
			$query->where('estado', $request->estado);
		}

		// Filtrar por solicitudes que requieren compra
		if ($request->has('requiereCompra') && $request->boolean('requiereCompra')) {
			$query->whereHas('items', function ($q) {
				$q->where('requiere_compra', true);
			});
		}

		$solicitudes = $query->get()->map(function ($solicitud) {
			return [
				'id' => (string) $solicitud->id_solicitud,
				'numeroSolicitud' => $solicitud->numero_solicitud,
				'proyecto' => [
					'id' => $solicitud->cod_proy,
					'nombre' => $solicitud->proyecto->nombre_proyecto ?? null,
				],
				'solicitadoPor' => [
					'id' => (string) $solicitud->solicitado_por,
					'nombre' => $solicitud->solicitadoPor->nombre_completo ?? null,
				],
				'fechaSolicitud' => $solicitud->fecha_solicitud?->toIso8601String(),
				'fechaRequerida' => $solicitud->fecha_requerida?->toDateString(),
				'estado' => $solicitud->estado,
				'urgente' => $solicitud->urgente,
				'motivo' => $solicitud->motivo,
				'itemsCount' => $solicitud->items->count(),
				'requiereCompra' => $solicitud->requiere_compra,
				'itemsRequierenCompra' => $solicitud->items_requieren_compra,
				'porcentajeEntregado' => $solicitud->porcentaje_entregado,
			];
		});

		return response()->json($solicitudes);
	}

	public function show(string $id)
	{
		$solicitud = SolicitudMaterial::with([
			'proyecto',
			'solicitadoPor',
			'aprobadaPor',
			'items.material',
			'items.lote',
		])->findOrFail($id);

		return response()->json([
			'id' => (string) $solicitud->id_solicitud,
			'numeroSolicitud' => $solicitud->numero_solicitud,
			'proyecto' => [
				'id' => $solicitud->cod_proy,
				'nombre' => $solicitud->proyecto->nombre_proyecto ?? null,
			],
			'solicitadoPor' => [
				'id' => (string) $solicitud->solicitado_por,
				'nombre' => $solicitud->solicitadoPor->nombre_completo ?? null,
				'cargo' => $solicitud->cargo_solicitante,
			],
			'aprobadaPor' => $solicitud->aprobada_por ? [
				'id' => (string) $solicitud->aprobada_por,
				'nombre' => $solicitud->aprobadaPor->nombre_completo ?? null,
			] : null,
			'fechaSolicitud' => $solicitud->fecha_solicitud?->toIso8601String(),
			'fechaRequerida' => $solicitud->fecha_requerida?->toDateString(),
			'fechaAprobacion' => $solicitud->fecha_aprobacion?->toIso8601String(),
			'estado' => $solicitud->estado,
			'urgente' => $solicitud->urgente,
			'motivo' => $solicitud->motivo,
			'observaciones' => $solicitud->observaciones,
			'requiereCompra' => $solicitud->requiere_compra,
			'itemsRequierenCompra' => $solicitud->items_requieren_compra,
			'materialesFaltantes' => $solicitud->items
				->filter(function ($item) {
					return $item->requiere_compra === true;
				})
				->map(function ($item) {
					return [
						'material' => [
							'id' => (string) $item->id_material,
							'nombre' => $item->material->nombre_producto ?? null,
							'codigo' => $item->material->codigo_producto ?? null,
						],
						'cantidadFaltante' => (float) $item->cantidad_faltante,
						'cantidadDisponiblePadre' => $item->cantidad_disponible_padre !== null ? (float) $item->cantidad_disponible_padre : null,
						'cantidadSolicitada' => (float) $item->cantidad_solicitada,
						'unidad' => $item->unidad,
					];
				})
				->values(),
			'items' => $solicitud->items->map(function ($item) {
				return [
					'id' => (string) $item->id_item,
					'material' => [
						'id' => (string) $item->id_material,
						'nombre' => $item->material->nombre_producto ?? null,
						'codigo' => $item->material->codigo_producto ?? null,
					],
					'lote' => $item->id_lote ? [
						'id' => (string) $item->id_lote,
						'numeroLote' => $item->lote->numero_lote ?? null,
					] : null,
					'cantidadSolicitada' => (float) $item->cantidad_solicitada,
					'cantidadDisponiblePadre' => $item->cantidad_disponible_padre !== null ? (float) $item->cantidad_disponible_padre : null,
					'cantidadFaltante' => $item->cantidad_faltante !== null ? (float) $item->cantidad_faltante : null,
					'requiereCompra' => (bool) $item->requiere_compra,
					'cantidadAprobada' => $item->cantidad_aprobada ? (float) $item->cantidad_aprobada : null,
					'cantidadEntregada' => (float) $item->cantidad_entregada,
					'unidad' => $item->unidad,
					'justificacion' => $item->justificacion,
					'observaciones' => $item->observaciones,
				];
			}),
			'porcentajeEntregado' => $solicitud->porcentaje_entregado,
		]);
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'projectId' => ['required', 'string', 'exists:proyectos,cod_proy'],
			'items' => ['required', 'array', 'min:1'],
			'items.*.materialId' => ['required'],
			'items.*.qty' => ['required', 'numeric', 'min:0.01'],
		]);

		// Obtener usuario logueado
		$user = $request->user();
		if (!$user) {
			return response()->json(['message' => 'Usuario no autenticado'], 401);
		}

		// Obtener empleado asociado al usuario
		$empleado = $user->empleado;
		if (!$empleado) {
			return response()->json(['message' => 'No se encontró un empleado asociado al usuario'], 404);
		}

		// Buscar almacén asociado al proyecto
		$almacen = \App\Models\Almacen::where('cod_proy', $data['projectId'])
			->where('activo', true)
			->first();

		if (!$almacen) {
			return response()->json([
				'message' => 'No se encontró un almacén activo para este proyecto'
			], 404);
		}

		// Obtener almacén padre si existe
		$almacenPadre = null;
		if ($almacen->id_almacen_padre) {
			$almacenPadre = \App\Models\Almacen::where('id_almacen', $almacen->id_almacen_padre)
				->where('activo', true)
				->first();
		}

		// Obtener IDs de materiales válidos en el almacén del proyecto
		$materialesValidos = \App\Models\StockAlmacen::where('id_almacen', $almacen->id_almacen)
			->whereHas('material', function ($query) {
				$query->where('activo', true);
			})
			->pluck('id_material')
			->toArray();

		if (empty($materialesValidos)) {
			return response()->json([
				'message' => 'El almacén del proyecto no tiene materiales disponibles'
			], 422);
		}

		// Validar que todos los materiales solicitados existan en el almacén del proyecto
		$materialesSolicitados = collect($data['items'])->pluck('materialId')->unique()->toArray();
		$materialesInvalidos = array_diff($materialesSolicitados, $materialesValidos);

		if (!empty($materialesInvalidos)) {
			$materialesNombres = Material::whereIn('id_material', $materialesInvalidos)
				->pluck('nombre_producto')
				->toArray();

			return response()->json([
				'message' => 'Algunos materiales no existen en el almacén del proyecto',
				'invalidMaterials' => $materialesNombres
			], 422);
		}

		// Crear la solicitud de materiales
		try {
			DB::beginTransaction();

			// Generar número de solicitud
			$ultimaSolicitud = SolicitudMaterial::orderBy('id_solicitud', 'desc')->first();
			$contadorSolicitud = $ultimaSolicitud ? (int) substr($ultimaSolicitud->numero_solicitud, -4) + 1 : 1;
			$numeroSolicitud = 'SOL-' . str_pad($contadorSolicitud, 4, '0', STR_PAD_LEFT);

			// Crear la solicitud
			$solicitud = SolicitudMaterial::create([
				'numero_solicitud' => $numeroSolicitud,
				'cod_proy' => $data['projectId'],
				'id_tarea' => $data['taskId'] ?? null,
				'solicitado_por' => $empleado->cod_empleado,
				'cargo_solicitante' => $empleado->cargo ?? null,
				'centro_costos' => 'CC-' . $data['projectId'],
				'fecha_solicitud' => now(),
				'fecha_requerida' => $data['requiredDate'] ?? now()->addDays(7),
				'estado' => 'pendiente',
				'requiere_aprobacion' => true,
				'motivo' => $data['reason'] ?? 'Solicitud de materiales',
				'observaciones' => $data['observations'] ?? null,
				'urgente' => $data['urgent'] ?? false,
			]);

			// Crear los items de la solicitud
			foreach ($data['items'] as $item) {
				$material = Material::findOrFail($item['materialId']);
				
				// Consultar stock en almacén padre si existe
				$cantidadDisponiblePadre = 0;
				$cantidadFaltante = 0;
				$requiereCompra = false;
				
				if ($almacenPadre) {
					$stockPadre = \App\Models\StockAlmacen::where('id_almacen', $almacenPadre->id_almacen)
						->where('id_material', $item['materialId'])
						->first();
					
					if ($stockPadre) {
						// Calcular cantidad disponible (disponible - reservada)
						$cantidadDisponiblePadre = max(0, $stockPadre->cantidad_disponible - $stockPadre->cantidad_reservada);
					}
					
					// Calcular cantidad faltante
					if ($cantidadDisponiblePadre < $item['qty']) {
						$cantidadFaltante = $item['qty'] - $cantidadDisponiblePadre;
						$requiereCompra = true;
					}
				} else {
					// Si no hay almacén padre, se asume que requiere compra completa
					$cantidadFaltante = $item['qty'];
					$requiereCompra = true;
				}
				
				SolicitudItem::create([
					'id_solicitud' => $solicitud->id_solicitud,
					'id_material' => $item['materialId'],
					'id_lote' => null,
					'cantidad_solicitada' => $item['qty'],
					'cantidad_disponible_padre' => $almacenPadre ? $cantidadDisponiblePadre : null,
					'cantidad_faltante' => $requiereCompra ? $cantidadFaltante : null,
					'requiere_compra' => $requiereCompra,
					'cantidad_aprobada' => null,
					'cantidad_entregada' => 0,
					'unidad' => $item['unit'] ?? $material->unidad_medida ?? 'unidad',
					'justificacion' => $item['justification'] ?? null,
					'observaciones' => $item['observations'] ?? null,
				]);
			}

			DB::commit();

			return response()->json([
				'id' => (string) $solicitud->id_solicitud,
				'numeroSolicitud' => $solicitud->numero_solicitud,
				'message' => 'Solicitud de materiales creada exitosamente'
			], 201);

		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'message' => 'Error al crear la solicitud de materiales',
				'error' => $e->getMessage()
			], 500);
		}
	}

	public function approve(Request $request, string $id)
	{
		$data = $request->validate([
			'action' => ['required', 'string', 'in:aprobar_con_compra,aprobar_solo_stock'],
			'observations' => ['nullable', 'string', 'max:500'],
		]);

		$user = $request->user();
		$empleado = $user->empleado;
		
		if (!$empleado) {
			return response()->json(['message' => 'No se encontró un empleado asociado al usuario'], 404);
		}

		$solicitud = SolicitudMaterial::with('items')->findOrFail($id);

		// Validar que la solicitud pueda ser aprobada
		if (!in_array($solicitud->estado, ['borrador', 'pendiente'])) {
			return response()->json([
				'message' => 'La solicitud no puede ser aprobada en su estado actual'
			], 422);
		}

		// Validar que tenga items
		if (!$solicitud->items || $solicitud->items->isEmpty()) {
			return response()->json([
				'message' => 'La solicitud no tiene materiales asociados'
			], 422);
		}

		try {
			DB::beginTransaction();

			// Actualizar estado de la solicitud
			$observaciones = $data['observations'] ?? null;
			$accionTexto = $data['action'] === 'aprobar_con_compra' 
				? 'Aprobada asumiendo compra de materiales faltantes'
				: 'Aprobada solo con stock disponible en almacén padre';
			
			$observacionesTexto = $observaciones 
				? ($solicitud->observaciones ? $solicitud->observaciones . "\n\n{$accionTexto}: " . $observaciones : "{$accionTexto}: " . $observaciones)
				: ($solicitud->observaciones ? $solicitud->observaciones . "\n\n{$accionTexto}" : $accionTexto);

			$solicitud->update([
				'estado' => 'aprobada',
				'fecha_aprobacion' => now(),
				'aprobada_por' => $empleado->cod_empleado,
				'observaciones' => $observacionesTexto,
			]);

			// Procesar items según la acción
			foreach ($solicitud->items as $item) {
				if ($data['action'] === 'aprobar_con_compra') {
					// Aprobar cantidad completa solicitada (asumiendo compra)
					$item->update([
						'cantidad_aprobada' => $item->cantidad_solicitada
					]);
				} elseif ($data['action'] === 'aprobar_solo_stock') {
					// Aprobar solo lo disponible en almacén padre
					$cantidadAprobar = $item->cantidad_disponible_padre ?? 0;
					$item->update([
						'cantidad_aprobada' => $cantidadAprobar
					]);
				}
			}

			DB::commit();

			return response()->json([
				'message' => 'Solicitud aprobada exitosamente',
				'id' => (string) $solicitud->id_solicitud,
				'estado' => $solicitud->estado,
				'action' => $data['action'],
			]);

		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'message' => 'Error al aprobar la solicitud',
				'error' => $e->getMessage()
			], 500);
		}
	}

	public function reject(Request $request, string $id)
	{
		$data = $request->validate([
			'observations' => ['required', 'string', 'max:500'],
		]);

		$user = $request->user();
		$empleado = $user->empleado;
		
		if (!$empleado) {
			return response()->json(['message' => 'No se encontró un empleado asociado al usuario'], 404);
		}

		$solicitud = SolicitudMaterial::findOrFail($id);

		// Validar que la solicitud pueda ser rechazada
		if (!in_array($solicitud->estado, ['borrador', 'pendiente'])) {
			return response()->json([
				'message' => 'La solicitud no puede ser rechazada en su estado actual'
			], 422);
		}

		try {
			DB::beginTransaction();

			$observacionesTexto = $solicitud->observaciones 
				? $solicitud->observaciones . "\n\nRechazo: " . $data['observations']
				: "Rechazo: " . $data['observations'];

			$solicitud->update([
				'estado' => 'rechazada',
				'fecha_aprobacion' => now(),
				'aprobada_por' => $empleado->cod_empleado,
				'observaciones' => $observacionesTexto,
			]);

			DB::commit();

			return response()->json([
				'message' => 'Solicitud rechazada exitosamente',
				'id' => (string) $solicitud->id_solicitud,
				'estado' => $solicitud->estado,
			]);

		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'message' => 'Error al rechazar la solicitud',
				'error' => $e->getMessage()
			], 500);
		}
	}

	public function deliver(Request $request, string $id)
	{
		$data = $request->validate([
			'deliveries' => ['required', 'array', 'min:1'],
			'deliveries.*.itemId' => ['required', 'integer', 'exists:solicitud_items,id_item'],
			'deliveries.*.quantity' => ['required', 'numeric', 'min:0.01'],
			'deliveries.*.lotId' => ['nullable', 'integer', 'exists:lote_material,id_lote'],
			'deliveries.*.lotNumber' => ['nullable', 'string', 'max:255'],
			'deliveries.*.observations' => ['nullable', 'string', 'max:500'],
		]);

		$user = $request->user();
		$empleado = $user->empleado;
		
		if (!$empleado) {
			return response()->json(['message' => 'No se encontró un empleado asociado al usuario'], 404);
		}

		$solicitud = SolicitudMaterial::with('items.material', 'proyecto')->findOrFail($id);

		// Validar que la solicitud esté aprobada
		if ($solicitud->estado !== 'aprobada' && $solicitud->estado !== 'enviado') {
			return response()->json([
				'message' => 'Solo se pueden registrar entregas para solicitudes aprobadas'
			], 422);
		}

		// Buscar almacén del proyecto (destino)
		$almacenDestino = \App\Models\Almacen::where('cod_proy', $solicitud->cod_proy)
			->where('activo', true)
			->first();

		if (!$almacenDestino) {
			return response()->json([
				'message' => 'No se encontró un almacén activo para el proyecto'
			], 404);
		}

		// Buscar almacén origen (almacén padre)
		$almacenOrigen = null;
		if ($almacenDestino->id_almacen_padre) {
			$almacenOrigen = \App\Models\Almacen::where('id_almacen', $almacenDestino->id_almacen_padre)
				->where('activo', true)
				->first();
		}

		if (!$almacenOrigen) {
			return response()->json([
				'message' => 'No se encontró un almacén origen (almacén padre) para realizar el despacho'
			], 404);
		}

		try {
			DB::beginTransaction();

			$itemsActualizados = [];
			$lotesCreados = [];
			$entregasCreadas = [];

			foreach ($data['deliveries'] as $delivery) {
				$item = $solicitud->items->find($delivery['itemId']);
				
				if (!$item) {
					throw new \Exception("Item {$delivery['itemId']} no pertenece a esta solicitud");
				}

				// Validar que la cantidad entregada no exceda la cantidad aprobada
				$cantidadAprobada = $item->cantidad_aprobada ?? $item->cantidad_solicitada;
				$nuevaCantidadEntregada = $item->cantidad_entregada + $delivery['quantity'];

				if ($nuevaCantidadEntregada > $cantidadAprobada) {
					throw new \Exception("La cantidad entregada ({$nuevaCantidadEntregada}) excede la cantidad aprobada ({$cantidadAprobada}) para el material {$item->material->nombre_producto}");
				}

				// Determinar tipo de entrega
				$tipoEntrega = $delivery['quantity'] >= $cantidadAprobada ? 'completa' : 'parcial';
				$motivoParcial = $tipoEntrega === 'parcial' ? ($delivery['observations'] ?? 'Entrega parcial') : null;

				// Manejar lote si se proporciona
				$loteId = $delivery['lotId'] ?? null;
				
				if (!$loteId && !empty($delivery['lotNumber'])) {
					// Crear nuevo lote si se proporciona número de lote
					$lote = \App\Models\LoteMaterial::create([
						'id_material' => $item->id_material,
						'numero_lote' => $delivery['lotNumber'],
						'fecha_ingreso' => now(),
						'estado_lote' => 'disponible',
					]);
					$loteId = $lote->id_lote;
					$lotesCreados[] = $lote;
				}

				// Verificar stock disponible en almacén origen
				$stockOrigen = \App\Models\StockAlmacen::where('id_almacen', $almacenOrigen->id_almacen)
					->where('id_material', $item->id_material)
					->when($loteId, function ($query) use ($loteId) {
						return $query->where('id_lote', $loteId);
					}, function ($query) {
						return $query->whereNull('id_lote');
					})
					->first();

				if (!$stockOrigen || ($stockOrigen->cantidad_disponible - $stockOrigen->cantidad_reservada) < $delivery['quantity']) {
					throw new \Exception("No hay stock suficiente en el almacén origen para el material {$item->material->nombre_producto}. Disponible: " . ($stockOrigen ? ($stockOrigen->cantidad_disponible - $stockOrigen->cantidad_reservada) : 0));
				}

				// Disminuir stock en almacén origen
				$stockOrigen->decrement('cantidad_disponible', $delivery['quantity']);

				// Aumentar stock en almacén destino
				$stockDestino = \App\Models\StockAlmacen::where('id_almacen', $almacenDestino->id_almacen)
					->where('id_material', $item->id_material)
					->when($loteId, function ($query) use ($loteId) {
						return $query->where('id_lote', $loteId);
					}, function ($query) {
						return $query->whereNull('id_lote');
					})
					->first();

				if ($stockDestino) {
					$stockDestino->increment('cantidad_disponible', $delivery['quantity']);
				} else {
					// Crear nuevo registro de stock si no existe
					\App\Models\StockAlmacen::create([
						'id_almacen' => $almacenDestino->id_almacen,
						'id_material' => $item->id_material,
						'id_lote' => $loteId,
						'cantidad_disponible' => $delivery['quantity'],
						'cantidad_reservada' => 0,
						'cantidad_minima_alerta' => $item->material->stock_minimo ?? 0,
					]);
				}

				// Generar número de entrega
				$ultimaEntrega = MaterialDelivery::orderBy('id_entrega', 'desc')->first();
				$contadorEntrega = $ultimaEntrega ? (int) substr($ultimaEntrega->numero_entrega, -4) + 1 : 1;
				$numeroEntrega = 'ENT-' . str_pad($contadorEntrega, 4, '0', STR_PAD_LEFT);

				// Crear registro de entrega (MaterialDelivery)
				$materialDelivery = MaterialDelivery::create([
					'numero_entrega' => $numeroEntrega,
					'id_solicitud' => $solicitud->id_solicitud,
					'id_item' => $item->id_item,
					'id_material' => $item->id_material,
					'id_lote' => $loteId,
					'id_almacen_origen' => $almacenOrigen->id_almacen,
					'id_almacen_destino' => $almacenDestino->id_almacen,
					'cantidad_entregada' => $delivery['quantity'],
					'cantidad_aprobada' => $cantidadAprobada,
					'tipo_entrega' => $tipoEntrega,
					'motivo_parcial' => $motivoParcial,
					'fecha_entrega' => now(),
					'entregado_por' => $empleado->cod_empleado,
					'recibido_por' => null, // Se actualizará cuando se reciba
					'observaciones' => $delivery['observations'] ?? null,
					'estado' => 'en_transito',
					'fecha_recepcion' => null,
				]);

				$entregasCreadas[] = $materialDelivery;

				// Registrar movimiento de inventario (transferencia)
				DB::table('movimientos_inventario')->insert([
					'numero_movimiento' => 'MOV-' . now()->format('YmdHis') . '-' . rand(1000, 9999),
					'id_material' => $item->id_material,
					'id_lote' => $loteId,
					'id_almacen_origen' => $almacenOrigen->id_almacen,
					'id_almacen_destino' => $almacenDestino->id_almacen,
					'tipo_movimiento' => 'transferencia',
					'cantidad' => $delivery['quantity'],
					'referencia' => $solicitud->numero_solicitud,
					'motivo' => "Despacho de solicitud {$solicitud->numero_solicitud} - Entrega {$numeroEntrega}",
					'fecha_movimiento' => now(),
					'registrado_por' => $empleado->cod_empleado,
					'created_at' => now(),
				]);

				// Actualizar cantidad entregada del item
				$item->update([
					'cantidad_entregada' => $nuevaCantidadEntregada,
					'id_lote' => $loteId ?? $item->id_lote,
					'observaciones' => $delivery['observations'] 
						? ($item->observaciones ? $item->observaciones . "\n\nEntrega {$numeroEntrega}: " . $delivery['observations'] : "Entrega {$numeroEntrega}: " . $delivery['observations'])
						: $item->observaciones,
				]);

				$itemsActualizados[] = [
					'itemId' => $item->id_item,
					'cantidadEntregada' => (float) $item->cantidad_entregada,
					'numeroEntrega' => $numeroEntrega,
				];
			}

			// Registrar evento en historial
			$this->registrarEventoHistorial($solicitud, 'entregado', $user, $empleado, [
				'entregas' => count($entregasCreadas),
				'items' => count($itemsActualizados),
				'almacen_origen' => $almacenOrigen->nombre,
				'almacen_destino' => $almacenDestino->nombre,
			], "Despacho realizado desde {$almacenOrigen->nombre} hacia {$almacenDestino->nombre}");

			// Actualizar estado de la solicitud según el porcentaje entregado
			$solicitud->refresh();
			$porcentajeEntregado = $solicitud->porcentaje_entregado;
			$estadoAnterior = $solicitud->estado;

			if ($porcentajeEntregado >= 100) {
				$solicitud->update(['estado' => 'recibida']);
				// Registrar evento de recepción completa
				$this->registrarEventoHistorial($solicitud, 'recibida', $user, $empleado, [
					'porcentaje_entregado' => $porcentajeEntregado,
				], "Solicitud recibida completamente");
			} elseif ($porcentajeEntregado > 0 && $estadoAnterior === 'aprobada') {
				$solicitud->update(['estado' => 'enviado']);
			}

			DB::commit();

			return response()->json([
				'message' => 'Despacho registrado exitosamente',
				'solicitudId' => (string) $solicitud->id_solicitud,
				'estado' => $solicitud->estado,
				'porcentajeEntregado' => $porcentajeEntregado,
				'itemsActualizados' => $itemsActualizados,
				'lotesCreados' => count($lotesCreados),
				'entregas' => array_map(function ($entrega) {
					return [
						'numeroEntrega' => $entrega->numero_entrega,
						'idEntrega' => $entrega->id_entrega,
					];
				}, $entregasCreadas),
				'almacenOrigen' => [
					'id' => $almacenOrigen->id_almacen,
					'nombre' => $almacenOrigen->nombre,
				],
				'almacenDestino' => [
					'id' => $almacenDestino->id_almacen,
					'nombre' => $almacenDestino->nombre,
				],
			]);

		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'message' => 'Error al registrar la entrega',
				'error' => $e->getMessage()
			], 500);
		}
	}

	/**
	 * Registra un evento en el historial de la solicitud
	 */
	private function registrarEventoHistorial(
		SolicitudMaterial $solicitud,
		string $tipoEvento,
		$usuario,
		Empleado $empleado,
		array $detalles = [],
		?string $observaciones = null
	): void {
		SolicitudHistorial::create([
			'id_solicitud' => $solicitud->id_solicitud,
			'tipo_evento' => $tipoEvento,
			'descripcion' => $this->getDescripcionEvento($tipoEvento, $solicitud),
			'detalles' => json_encode($detalles),
			'usuario_id' => $usuario->id,
			'empleado_id' => $empleado->cod_empleado,
			'fecha_evento' => now(),
			'observaciones' => $observaciones,
		]);
	}

	/**
	 * Obtiene la descripción del evento según su tipo
	 */
	private function getDescripcionEvento(string $tipoEvento, SolicitudMaterial $solicitud): string
	{
		$descripciones = [
			'creada' => "Solicitud {$solicitud->numero_solicitud} creada",
			'aprobada' => "Solicitud {$solicitud->numero_solicitud} aprobada",
			'aprobada_con_compra' => "Solicitud {$solicitud->numero_solicitud} aprobada asumiendo compra de materiales",
			'aprobada_solo_stock' => "Solicitud {$solicitud->numero_solicitud} aprobada solo con stock disponible",
			'rechazada' => "Solicitud {$solicitud->numero_solicitud} rechazada",
			'enviado' => "Solicitud {$solicitud->numero_solicitud} enviada",
			'entregado' => "Despacho de solicitud {$solicitud->numero_solicitud} realizado",
			'recibida' => "Solicitud {$solicitud->numero_solicitud} recibida completamente",
			'cancelada' => "Solicitud {$solicitud->numero_solicitud} cancelada",
		];

		return $descripciones[$tipoEvento] ?? "Evento {$tipoEvento} en solicitud {$solicitud->numero_solicitud}";
	}
}


