<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Statistics;

use App\Contracts\Http\Requests\HasOwnerId;
use App\Http\Requests\AbstractEmptyRequest;
use App\Http\Requests\Traits\HasOwnerIdTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SpacePerUserRequest extends AbstractEmptyRequest implements HasOwnerId
{
	use HasOwnerIdTrait;

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
	protected function processValidatedValues(array $values, array $files): void
	{
		// Filter only to user if user is not admin
		if (Auth::check() && Auth::user()?->may_administrate !== true) {
			$this->owner_id = intval(Auth::id());
		}
	}
}
