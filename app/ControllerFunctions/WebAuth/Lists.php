<?php

namespace App\ControllerFunctions\WebAuth;

use Illuminate\Support\Facades\Auth;

class Lists
{
	public function do()
	{
		return Auth::user()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
