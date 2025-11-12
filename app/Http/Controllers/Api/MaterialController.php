<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
	public function catalog()
	{
		return Material::select('id', 'nombre as name', 'codigo as sku', 'unidad as unit')->get();
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


