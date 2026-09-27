<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\WebAuthn;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laragear\WebAuthn\Models\WebAuthnCredential;

class EditCredentialRequest extends BaseApiRequest
{
	private WebAuthnCredential $credential;
	private string $alias;

	/**
	 * {@inheritDoc}
	 *
	 * The credential is resolved from a client-provided ID, and credential IDs
	 * are not secret: the unauthenticated `WebAuthn::login/options` endpoint
	 * hands them out for any given username.
	 * Hence, being allowed to edit one's own settings is not sufficient; the
	 * caller must also own the credential, unless they are an administrator.
	 */
	public function authorize(): bool
	{
		if (!Gate::check(UserPolicy::CAN_EDIT, [User::class])) {
			return false;
		}

		/** @var User $user */
		$user = Auth::user();

		return $user->may_administrate === true || $this->isOwnedBy($user);
	}

	/**
	 * Checks whether the resolved credential belongs to the given user.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	private function isOwnedBy(User $user): bool
	{
		return $user->webAuthnCredentials()->whereKey($this->credential->getKey())->exists();
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
