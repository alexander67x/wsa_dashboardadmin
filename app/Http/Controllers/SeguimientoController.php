<?php

namespace App\Http\Controllers;

use App\Services\SeguimientoService;

class SeguimientoController extends Controller
{
    public function __construct(
        private readonly SeguimientoService $service,
    ) {
    }

    public function index()
    {
        $summary = $this->service->getTrackingSummary();

        return view('seguimiento', $summary);
    }
}
