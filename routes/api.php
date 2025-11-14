<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\KanbanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are intended for the mobile app. For production, protect
| them with Sanctum tokens. Login is public; the rest requires auth.
|
*/

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
	// Auth
	Route::get('/auth/me', [AuthController::class, 'me']);
	Route::post('/auth/logout', [AuthController::class, 'logout']);

	// Projects
	Route::get('/projects', [ProjectController::class, 'index']);
	Route::get('/projects/{id}', [ProjectController::class, 'show']);
	Route::get('/projects/{id}/team', [ProjectController::class, 'team']);
	Route::get('/projects/{id}/stock', [ProjectController::class, 'stock']);

	// Reports
	Route::get('/reports', [ReportController::class, 'index']);
	Route::get('/reports/{id}', [ReportController::class, 'show']);
	Route::post('/reports', [ReportController::class, 'store']);

	// Materials
	Route::get('/materials/catalog', [MaterialController::class, 'catalog']);
	Route::get('/materials/requests', [MaterialController::class, 'index']);
	Route::post('/materials/requests', [MaterialController::class, 'store']);

	// Kanban
	Route::get('/kanban', [KanbanController::class, 'board']);
	Route::post('/kanban/columns', [KanbanController::class, 'addColumn']);
	Route::post('/kanban/cards', [KanbanController::class, 'addCard']);
	Route::get('/kanban/cards/{id}', [KanbanController::class, 'showCard']);
});


