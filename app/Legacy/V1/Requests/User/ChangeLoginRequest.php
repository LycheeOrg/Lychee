<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\User;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPassword;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPasswordTrait;
use App\Legacy\V1\RuleSets\User\ChangeLoginRuleSet;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

final class ChangeLoginRequest extends BaseApiRequest implements HasPassword
{
	use HasPasswordTrait;

	protected string $oldPassword;
	protected ?string $username = null;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT, [User::class]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return ChangeLoginRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->password = $values[RequestAttribute::PASSWORD_ATTRIBUTE];
		$this->oldPassword = $values[RequestAttribute::OLD_PASSWORD_ATTRIBUTE];

		// We do not allow '' as a username. So any such input will be cast to null
		if (array_key_exists(RequestAttribute::USERNAME_ATTRIBUTE, $values)) {
			$this->username = trim($values[RequestAttribute::USERNAME_ATTRIBUTE]);
			$this->username = $this->username === '' ? null : $this->username;
		} else {
			// @codeCoverageIgnoreStart
			$this->username = null;
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Returns the previous password.
	 *
	 * See {@link HasPasswordTrait::password()} for an explanation of the
	 * semantic difference between the return values `null` and `''`.
	 *
	 * @return string|null
	 */
	public function oldPassword(): ?string
	{
		return $this->oldPassword;
	}

	/**
	 * Return the new username chosen.
	 * if Username is null, this means that the user does not want to update it.
	 *
	 * @return ?string
	 */
	public function username(): ?string
	{
		return $this->username;
	}
}