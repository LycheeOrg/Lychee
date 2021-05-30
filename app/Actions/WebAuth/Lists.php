<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;

class Lists
{
	public function do()
	{
		return AccessControl::user()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
