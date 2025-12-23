<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Profile;

use App\Contracts\Http\Requests\HasEmail;
use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasEmailTrait;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Support\Facades\Auth;

class RegistrationRequest extends BaseApiRequest implements HasUsername, HasPassword, HasEmail
{
	use HasUsernameTrait;
	use HasPasswordTrait;
	use HasEmailTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (Auth::check()) {
			return false;
		}

		// @phpstan-ignore staticMethod.dynamicCall
		return $this->configs()->getValueAsBool('user_registration_enabled') || $this->hasValidSignature();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::EMAIL_ATTRIBUTE => ['required', 'string', 'email', 'max:255', 'unique:users'],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', 'confirmed', new PasswordRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE];
	}
}
