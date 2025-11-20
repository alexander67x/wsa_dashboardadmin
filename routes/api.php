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
	Route::get('/projects', [ProjectController::class, 'index'])
		->middleware('permission:dashboard.projects.overview,projects.detail.view,projects.my.view');
	Route::get('/projects/my-projects', [ProjectController::class, 'myProjects'])
		->middleware('permission:projects.my.view');
	Route::get('/projects/task-responsibles', [ProjectController::class, 'taskResponsibles'])
		->middleware('permission:tasks.assign,projects.my.view');
	Route::get('/projects/{id}', [ProjectController::class, 'show'])
		->middleware('permission:projects.detail.view,projects.my.view');
	Route::get('/projects/{id}/team', [ProjectController::class, 'team'])
		->middleware('permission:projects.detail.view,projects.my.view');
	Route::get('/projects/{id}/stock', [ProjectController::class, 'stock'])
		->middleware('permission:inventory.view.project,inventory.view.central');

	// Reports
	Route::get('/reports', [ReportController::class, 'index'])
		->middleware('permission:reports.view,projects.my.view');
	Route::get('/reports/{id}', [ReportController::class, 'show'])
		->middleware('permission:reports.view,projects.my.view');
	Route::post('/reports', [ReportController::class, 'store'])
		->middleware('permission:reports.create,mobile.tasks.execute');

	// Incidencias
	Route::get('/incidencias', [IncidenciaController::class, 'index'])
		->middleware('permission:incidents.view,incidents.review.project,incidents.review.impact');
	Route::get('/incidencias/{id}', [IncidenciaController::class, 'show'])
		->middleware('permission:incidents.view,incidents.review.project,incidents.review.impact');
	Route::post('/incidencias', [IncidenciaController::class, 'store'])
		->middleware('permission:incidents.create,mobile.incidents.report,incidents.record.high');

	// Tasks
	Route::get('/tasks', [TaskController::class, 'index'])
		->middleware('permission:tasks.view,projects.my.view');
	Route::get('/tasks/{id}', [TaskController::class, 'show'])
		->middleware('permission:tasks.view,projects.my.view');
	Route::post('/tasks/{id}/assign-to-me', [TaskController::class, 'assignToMe'])
		->middleware('permission:mobile.tasks.execute,tasks.assign');

	// Materials
	Route::get('/materials/catalog', [MaterialController::class, 'catalog'])
		->middleware('permission:inventory.view.project,inventory.view.central,projects.my.view');
	Route::get('/materials/requests', [MaterialController::class, 'index'])
		->middleware('permission:inventory.view.project,inventory.view.central');
	Route::post('/materials/requests', [MaterialController::class, 'store'])
		->middleware('permission:materials.requests.create,materials.requests.coordinate');
	Route::get('/materials/requests/{id}', [MaterialController::class, 'show'])
		->middleware('permission:inventory.view.project,inventory.view.central,projects.my.view');
	Route::post('/materials/requests/{id}/approve', [MaterialController::class, 'approve'])
		->middleware('permission:materials.requests.approve');
	Route::post('/materials/requests/{id}/reject', [MaterialController::class, 'reject'])
		->middleware('permission:materials.requests.approve');
	Route::post('/materials/requests/{id}/deliver', [MaterialController::class, 'deliver'])
		->middleware('permission:materials.requests.deliver,inventory.movements.transfers');

	// Kanban
	Route::get('/kanban', [KanbanController::class, 'board'])
		->middleware('permission:tasks.view,projects.my.view');
	Route::post('/kanban/columns', [KanbanController::class, 'addColumn'])
		->middleware('permission:tasks.assign');
	Route::post('/kanban/cards', [KanbanController::class, 'addCard'])
		->middleware('permission:tasks.assign');
	Route::get('/kanban/cards/{id}', [KanbanController::class, 'showCard'])
		->middleware('permission:tasks.view,projects.my.view');

	// Push notifications
	Route::post('/push/register', [PushController::class, 'register'])
		->middleware('permission:mobile.tasks.view,projects.my.view,projects.detail.view');
});
