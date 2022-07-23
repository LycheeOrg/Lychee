<?php

namespace App\Actions\WebAuth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Lists
{
	public function do(): Collection
	{
		return Auth::authenticate()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
