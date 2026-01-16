<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Profile;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\UserSharedAlbumsVisibility;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class UpdateSharedAlbumsVisibilityRequest extends BaseApiRequest
{
	protected UserSharedAlbumsVisibility $sharedAlbumsVisibility;

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
		return [
			RequestAttribute::SHARED_ALBUMS_VISIBILITY_ATTRIBUTE => ['required', new Enum(UserSharedAlbumsVisibility::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->sharedAlbumsVisibility = UserSharedAlbumsVisibility::from($values[RequestAttribute::SHARED_ALBUMS_VISIBILITY_ATTRIBUTE]);
	}

	public function sharedAlbumsVisibility(): UserSharedAlbumsVisibility
	{
		return $this->sharedAlbumsVisibility;
	}
}
