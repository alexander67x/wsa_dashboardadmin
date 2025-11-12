<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
	public function login(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
		]);

		$user = User::where('email', $credentials['email'])->first();
		if (! $user || ! Hash::check($credentials['password'], $user->password)) {
			return response()->json(['message' => 'Credenciales inválidas'], 422);
		}

		$token = $user->createToken('mobile')->plainTextToken;

		return response()->json([
			'token' => $token,
			'role' => 'worker',
			'user' => [
				'id' => (string) $user->id,
				'name' => $user->name,
			],
		]);
	}

	public function me(Request $request)
	{
		$user = $request->user();
		return [
			'id' => (string) $user->id,
			'name' => $user->name,
		];
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()?->delete();
		return response()->noContent();
	}
}


