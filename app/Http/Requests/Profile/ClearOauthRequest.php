<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Profile;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\OauthProvidersType;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class ClearOauthRequest extends BaseApiRequest
{
	private OauthProvidersType $provider;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_EDIT, [User::class]);
	}

	public function rules(): array
	{
		return [
			RequestAttribute::PROVIDER_ATTRIBUTE => ['required', 'string', new Enum(OauthProvidersType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->provider = OauthProvidersType::from($values[RequestAttribute::PROVIDER_ATTRIBUTE]);
	}

	public function provider(): OauthProvidersType
	{
		return $this->provider;
	}
}
