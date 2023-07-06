<?php

namespace App\Actions\User;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TokenReset
{
	/**
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(): string
	{
		/** @var User $user */
		$user = Auth::user();
		$token = strtr(base64_encode(random_bytes(16)), '+/', '-_');
		$user->token = hash('SHA512', $token);
		$user->save();

		return $token;
	}
}
