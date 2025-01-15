<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\WebAuthn;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class DeleteCredentialRequest extends BaseApiRequest
{
	private string $id;

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
			RequestAttribute::ID_ATTRIBUTE => 'required|string',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->id = $values[RequestAttribute::ID_ATTRIBUTE];
	}

	public function getId(): string
	{
		return $this->id;
	}
}
