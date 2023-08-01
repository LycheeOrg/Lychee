<?php

namespace App\Rules;

use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordRule implements ValidationRule
{
	use ValidateTrait;

	/**
	 * {@inheritDoc}
	 */
	public function passes(string $attribute, mixed $value): bool
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
