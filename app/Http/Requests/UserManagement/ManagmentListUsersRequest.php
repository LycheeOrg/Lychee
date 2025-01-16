<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\UserManagement;

use App\Contracts\Http\Requests\HasSeStatusBoolean;
use App\Http\Requests\AbstractEmptyRequest;
use App\Http\Requests\Traits\HasSeStatusBooleanTrait;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class ManagmentListUsersRequest extends AbstractEmptyRequest implements HasSeStatusBoolean
{
	use HasSeStatusBooleanTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);
	}
}
