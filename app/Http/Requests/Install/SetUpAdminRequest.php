<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Install;

use App\Contracts\Http\Requests\HasPassword;
use App\Contracts\Http\Requests\HasUsername;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Exceptions\AdminUserAlreadySetException;
use App\Http\Middleware\Checks\HasAdminUser;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Http\Requests\Traits\HasUsernameTrait;
use App\Rules\PasswordRule;
use App\Rules\UsernameRule;
use Illuminate\Foundation\Http\FormRequest;

class SetUpAdminRequest extends FormRequest implements HasUsername, HasPassword
{
	use HasUsernameTrait;
	use HasPasswordTrait;

	protected $errorBag = 'errors';

	/**
	 * @return array<string,array<int,string|\Illuminate\Contracts\Validation\ValidationRule>>
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::USERNAME_ATTRIBUTE => ['required', new UsernameRule()],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', 'confirmed', new PasswordRule(false)],
		];
	}

	/**
	 * This Request is only available if the application is not installed yet.
	 *
	 * @return bool
	 *
	 * @throws AdminUserAlreadySetException if an admin user already exists
	 */
	public function authorize(HasAdminUser $has_admin_user): bool
	{
		if ($has_admin_user->assert()) {
			throw new AdminUserAlreadySetException();
		}

		return true;
	}

	public function passedValidation()
	{
		$values = $this->validated();
		$this->username = $values[RequestAttribute::USERNAME_ATTRIBUTE];
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
	}
}
