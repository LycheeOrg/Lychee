<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Support\Facades\Auth;

class Delete
{
	public function do(string|array $ids): void
	{
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->removeCredential($ids);
	}
}
