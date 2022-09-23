<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Lists
{
	/**
	 * @throws UnauthenticatedException
	 */
	public function do(): Collection
	{
		/** @var \App\Models\User */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return $user->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
