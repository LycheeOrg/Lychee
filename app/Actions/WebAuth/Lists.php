<?php

namespace App\Actions\WebAuth;

use AccessControl;

class Lists
{
	public function do()
	{
		return AccessControl::user()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
