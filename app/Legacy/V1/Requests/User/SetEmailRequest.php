<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\User;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\RuleSets\User\SetEmailRuleSet;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

final class SetEmailRequest extends BaseApiRequest
{
	protected ?string $email = null;

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
		return SetEmailRuleSet::rules();
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->email = $values[RequestAttribute::EMAIL_ATTRIBUTE];
	}

	public function email(): ?string
	{
		return $this->email;
	}
}
