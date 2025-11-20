<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
	public function login(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
		]);

		$user = User::where('email', $credentials['email'])->first();
		
		// Si el usuario no existe, retornar error genérico
		if (! $user) {
			return response()->json(['message' => 'Credenciales inválidas'], 422);
		}

		// Verificar si el usuario está activo
		if (! $user->is_active) {
			return response()->json(['message' => 'Su cuenta ha sido desactivada. Contacte al administrador.'], 403);
		}

		// Verificar contraseña
		if (! Hash::check($credentials['password'], $user->password)) {
			// El email es correcto pero la contraseña es incorrecta
			$newAttempts = $user->failed_login_attempts + 1;
			
			// Si llega a 5 intentos fallidos, inactivar el usuario
			if ($newAttempts >= 5) {
				$user->update([
					'failed_login_attempts' => $newAttempts,
					'last_failed_login_at' => now(),
					'is_active' => false,
				]);
				return response()->json([
					'message' => 'Su cuenta ha sido desactivada debido a múltiples intentos fallidos de inicio de sesión. Contacte al administrador.'
				], 403);
			}

			$user->update([
				'failed_login_attempts' => $newAttempts,
				'last_failed_login_at' => now(),
			]);

			$remainingAttempts = 5 - $newAttempts;
			return response()->json([
				'message' => 'Credenciales inválidas',
				'attempts_remaining' => $remainingAttempts
			], 422);
		}

		// Login exitoso: resetear contador de intentos fallidos
		$user->update([
			'failed_login_attempts' => 0,
			'last_failed_login_at' => null,
		]);

		$token = $user->createToken('mobile')->plainTextToken;
		$empleado = $user->empleado;
		$role = $empleado?->role;

		return response()->json([
			'token' => $token,
			'role' => $role ? [
				'id' => (string) $role->id_role,
				'slug' => $role->slug ?? Str::slug($role->nombre),
				'nombre' => $role->nombre,
				'descripcion' => $role->descripcion,
			] : null,
			'permissions' => $empleado?->permissionCodes() ?? [],
			'user' => [
				'id' => (string) $user->id,
				'name' => $user->name,
				'employeeId' => $empleado?->cod_empleado ? (string) $empleado->cod_empleado : null,
			],
			'employee' => $empleado ? [
				'id' => (string) $empleado->cod_empleado,
				'name' => $empleado->nombre_completo,
				'position' => $empleado->cargo,
				'role' => $role?->nombre,
			] : null,
		]);
	}

	public function me(Request $request)
	{
		$user = $request->user();
		$empleado = $user->empleado;
		$role = $empleado?->role;
		
		return [
			'id' => (string) $user->id,
			'name' => $user->name,
			'role' => $role ? [
				'id' => $role->id_role,
				'slug' => $role->slug ?? Str::slug($role->nombre),
				'nombre' => $role->nombre,
				'descripcion' => $role->descripcion,
			] : null,
			'permissions' => $empleado?->permissionCodes() ?? [],
			'employeeId' => $empleado?->cod_empleado ? (string) $empleado->cod_empleado : null,
			'employee' => $empleado ? [
				'id' => (string) $empleado->cod_empleado,
				'name' => $empleado->nombre_completo,
				'position' => $empleado->cargo,
			] : null,
		];
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()?->delete();
		return response()->noContent();
	}
}
