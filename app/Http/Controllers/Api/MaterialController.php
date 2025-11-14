<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;

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

	public function index()
	{
		// TODO: Return real material requests
		return [];
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'projectId' => ['required'],
			'items' => ['required', 'array'],
			'items.*.materialId' => ['required'],
			'items.*.qty' => ['required', 'numeric', 'min:0.01'],
		]);

		// TODO: Persist request and items
		return response()->json(['id' => (string) now()->timestamp], 201);
	}
}


