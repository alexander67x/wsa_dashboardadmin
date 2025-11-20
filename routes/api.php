<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\IncidenciaController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\KanbanController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\TaskController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
	// Auth
	Route::get('/auth/me', [AuthController::class, 'me']);
	Route::post('/auth/logout', [AuthController::class, 'logout']);

	// Projects
	Route::get('/projects/my-projects', [ProjectController::class, 'myProjects']);
	Route::get('/projects/task-responsibles', [ProjectController::class, 'taskResponsibles']);
	Route::get('/projects/{id}', [ProjectController::class, 'show']);
	Route::get('/projects/{id}/team', [ProjectController::class, 'team']);
	Route::get('/projects/{id}/stock', [ProjectController::class, 'stock']);

	// Reports
	Route::get('/reports', [ReportController::class, 'index']);
	Route::get('/reports/{id}', [ReportController::class, 'show']);
	Route::post('/reports', [ReportController::class, 'store']);

	// Incidencias
	Route::get('/incidencias', [IncidenciaController::class, 'index']);
	Route::get('/incidencias/{id}', [IncidenciaController::class, 'show']);
	Route::post('/incidencias', [IncidenciaController::class, 'store']);

	// Tasks
	Route::get('/tasks', [TaskController::class, 'index']);
	Route::get('/tasks/{id}', [TaskController::class, 'show']);
	Route::post('/tasks/{id}/assign-to-me', [TaskController::class, 'assignToMe']);

	// Materials
	Route::get('/materials/catalog', [MaterialController::class, 'catalog']);
	Route::get('/materials/requests', [MaterialController::class, 'index']);
	Route::post('/materials/requests', [MaterialController::class, 'store']);
	Route::get('/materials/requests/{id}', [MaterialController::class, 'show']);
	Route::post('/materials/requests/{id}/approve', [MaterialController::class, 'approve']);
	Route::post('/materials/requests/{id}/reject', [MaterialController::class, 'reject']);
	Route::post('/materials/requests/{id}/deliver', [MaterialController::class, 'deliver']);

	// Kanban
	Route::get('/kanban', [KanbanController::class, 'board']);
	Route::post('/kanban/columns', [KanbanController::class, 'addColumn']);
	Route::post('/kanban/cards', [KanbanController::class, 'addCard']);
	Route::get('/kanban/cards/{id}', [KanbanController::class, 'showCard']);

	// Push notifications
	Route::post('/push/register', [PushController::class, 'register']);
});

