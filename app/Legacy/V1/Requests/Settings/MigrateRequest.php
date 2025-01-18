<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\AdminAuthentication;
use App\Legacy\V1\Contracts\Http\Requests\HasPassword;
use App\Legacy\V1\Contracts\Http\Requests\HasUsername;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPasswordTrait;
use App\Legacy\V1\Requests\Traits\HasUsernameTrait;
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
final class MigrateRequest extends BaseApiRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// This conditional code makes use of lazy boolean evaluation: a || b does not execute b if a is true.
		// 1. Check whether the user is already logged in properly
		// 2. Check if the admin user is registered and login as admin, if not
		// 3. Attempt to login as an admin user using the legacy method: hash(username) + hash(password).
		// 4. Try to login the normal way.
		//
		// TODO: Step 2 will become unnecessary once the admin user of any existing installation has at least logged in once and the admin user has therewith migrated to use a non-hashed user name
		$isLoggedIn = Auth::check();
		$isLoggedIn = $isLoggedIn || AdminAuthentication::loginAsAdmin($this->username(), $this->password(), $this->ip());
		$isLoggedIn = $isLoggedIn || Auth::attempt(['username' => $this->username(), 'password' => $this->password()]);

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
