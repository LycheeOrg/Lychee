<?php

namespace App\Actions\WebAuth;

use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Delete
{
	/**
	 * @throws UnauthenticatedException
	 */
	public function do(string|array $ids): void
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->removeCredential($ids);
	}
}
