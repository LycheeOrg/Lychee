<?php

namespace App\Rules;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return Hash::check($value, $user->password);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute is invalid.';
	}
}
