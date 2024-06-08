<?php

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\WebAuthn\DeleteCredentialRequest;
use App\Http\Requests\WebAuthn\ListCredentialsRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WebAuthnManageController
{
	/**
	 * @throws UnauthenticatedException
	 *
	 * @phpstan-ignore-next-line unused...
	 */
	public function list(ListCredentialsRequest $request): Collection
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return $user->webAuthnCredentials;
	}

	/**
	 * @throws UnauthenticatedException
	 */
	public function delete(DeleteCredentialRequest $request): void
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$user->webAuthnCredentials()->where('id', $request->id)->delete();
	}
}
