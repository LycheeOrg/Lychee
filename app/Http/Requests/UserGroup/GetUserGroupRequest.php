<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\UserGroup;

use App\Contracts\Http\Requests\HasUserGroup;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasUserGroupTrait;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Facades\Gate;

class GetUserGroupRequest extends BaseApiRequest implements HasUserGroup
{
	use HasUserGroupTrait;

	public function authorize(): bool
	{
		return Gate::check(UserGroupPolicy::CAN_READ, [UserGroup::class, $this->user_group]);
	}

	public function rules(): array
	{
		return [
			RequestAttribute::GROUP_ID => ['required', 'int'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->user_group = UserGroup::with('users')->findOrFail($values[RequestAttribute::GROUP_ID]);
	}
}
