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
use Laragear\WebAuthn\Models\WebAuthnCredential;

class EditCredentialRequest extends BaseApiRequest
{
	private WebAuthnCredential $credential;
	private string $alias;

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
			RequestAttribute::ALIAS_ATTRIBUTE => 'required|string|min:5|max:255',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $id */
		$id = $values[RequestAttribute::ID_ATTRIBUTE];
		$this->credential = WebAuthnCredential::query()->findOrFail($id);
		$this->alias = $values[RequestAttribute::ALIAS_ATTRIBUTE];
	}

	public function getCredential(): WebAuthnCredential
	{
		return $this->credential;
	}

	public function getAlias(): string
	{
		return $this->alias;
	}
}
