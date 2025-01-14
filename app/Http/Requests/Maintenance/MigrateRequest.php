<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Maintenance;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Request
 */
class MigrateRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$isLoggedIn = Auth::check() || Auth::attempt(['username' => $this->username(), 'password' => $this->password()]);

		// Check if logged in AND is admin
		return $isLoggedIn && Gate::check(SettingsPolicy::CAN_UPDATE, Configs::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['sometimes', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE] ?? '';
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE] ?? '';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function failedAuthorization(): void
	{
		throw new HttpResponseException(response()->view('update.error', ['code' => '403', 'message' => 'Incorrect username or password'], 403));
	}
}
