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
use App\Models\User;
use App\Policies\SettingsPolicy;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

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
		$is_logged_in = Auth::check();
		if (!$is_logged_in && ($this->username() !== '' || $this->password() !== '')) {
			try {
				$is_logged_in = Auth::attempt(['username' => $this->username(), 'password' => $this->password()]);
			} catch (\Throwable) {
				$user = User::without(['user_groups'])->where('username', $this->username())->first();
				if ($user === null) {
					// If the user does not exist, we do not authenticate
					return false;
				}

				if (Hash::check($this->password(), $user->password)) {
					// If the password matches, we log in the user
					Auth::login($user);
					$is_logged_in = true;
				}
			}
		}

		// Check if logged in AND is admin
		return $is_logged_in && Gate::check(SettingsPolicy::CAN_UPDATE, Configs::class);
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
