<?php

namespace App\Actions\WebAuth;

use App\Auth\Authorization;
use Illuminate\Support\Collection;

class Lists
{
	public function do(): Collection
	{
		return Authorization::userOrFail()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
