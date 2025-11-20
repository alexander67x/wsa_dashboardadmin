<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class PushController extends Controller
{
	public function register(Request $request)
	{
		$validated = $request->validate([
			'token' => ['required', 'string'],
		]);

		$user = $request->user();

		UserDevice::updateOrCreate(
			[
				'expo_token' => $validated['token'],
			],
			[
				'user_id' => $user->id,
			]
		);

		return response()->json(['success' => true]);
	}
}
