<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;
use Illuminate\Support\Collection;

class Lists
{
	public function do(): Collection
	{
		return AccessControl::user()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
