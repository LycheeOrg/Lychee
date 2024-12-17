<?php

namespace App\Http\Controllers\WebAuthn;

use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\WebAuthn\DeleteCredentialRequest;
use App\Http\Requests\WebAuthn\EditCredentialRequest;
use App\Http\Requests\WebAuthn\ListCredentialsRequest;
use App\Http\Resources\Models\WebAuthnResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WebAuthnManageController
{
	/**
	 * List all the WebAuthn users credentials.
	 *
	 * @param ListCredentialsRequest $request
	 *
	 * @return Collection<string|int, WebAuthnResource>
	 *
	 * @throws UnauthenticatedException
	 */
	public function list(ListCredentialsRequest $request): Collection
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return WebAuthnResource::collect($user->webAuthnCredentials);
	}

	/**
	 * Delete a WebAuthn credential.
	 *
	 * @throws UnauthenticatedException
	 */
	public function delete(DeleteCredentialRequest $request): void
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$user->webAuthnCredentials()->where('id', $request->getId())->delete();
	}

	/**
	 * Edit credential.
	 *
	 * @param EditCredentialRequest $request
	 *
	 * @return void
	 */
	public function edit(EditCredentialRequest $request): void
	{
		$credential = $request->getCredential();
		$credential->alias = $request->getAlias();
		$credential->save();
	}
}
