<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Session;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Exceptions\BasicAuthDisabledExecption;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Providers\AuthServiceProvider;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;

class LoginRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return AuthServiceProvider::isBasicAuthEnabled() || throw new BasicAuthDisabledExecption();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
	}
}
