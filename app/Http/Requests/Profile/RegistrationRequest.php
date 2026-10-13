<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

		return $this->configs()->getValueAsBool('user_registration_enabled') ||
			/** @phpstan-ignore staticMethod.dynamicCall  */
			$this->hasValidSignature();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		// With `email_login_enabled` the email alone identifies the account, so
		// the username may be omitted; `Actions\User\Create` then stores the
		// email as the username. `max:100` is the width of both `users.email`
		// and `users.username` (and what `UpdateProfileRequest` already
		// enforces), so an email that is accepted here always fits the username
		// column it may be copied into.
		$is_username_optional = $this->configs()->getValueAsBool('email_login_enabled');

		return [
			RequestAttribute::USERNAME_ATTRIBUTE => $is_username_optional
				? ['sometimes', 'nullable', new UsernameRule(true)]
				: ['required', new UsernameRule()],
			RequestAttribute::EMAIL_ATTRIBUTE => ['required', 'string', 'email', 'max:100'],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', 'confirmed', new PasswordRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE] ?? '';
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE];
	}
}
