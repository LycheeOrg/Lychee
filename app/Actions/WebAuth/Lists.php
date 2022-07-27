<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Lists
{
	public function do(): Collection
	{
		$user = Auth::user() ?? throw new UnauthenticatedException('User cannot be null');

		return $user->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
