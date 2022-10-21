<?php

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WebAuthnManageController
{
	/**
	 * @throws UnauthenticatedException
	 */
	public function list(): Collection
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return $user->webAuthnCredentials;
	}

	/**
	 * @throws UnauthenticatedException
	 */
	public function delete(Request $request): void
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$id = $request->validate(['id' => 'required|string']);
		$user->webAuthnCredentials()->where('id', $id)->delete();
	}
}
