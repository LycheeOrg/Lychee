<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\UserGroup;

use App\Contracts\Http\Requests\HasRole;
use App\Contracts\Http\Requests\HasUser;
use App\Contracts\Http\Requests\HasUserGroup;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\UserGroupRole;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasRoleTrait;
use App\Http\Requests\Traits\HasUserGroupTrait;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class ManageUserGroupRequest extends BaseApiRequest implements HasUserGroup, HasUser, HasRole
{
	use HasUserGroupTrait;
	use HasUserTrait;
	use HasRoleTrait;

	public function authorize(): bool
	{
		return Gate::check(UserGroupPolicy::CAN_ADD_OR_REMOVE_USER, [UserGroup::class, $this->user_group]);
	}

	public function rules(): array
	{
		return [
			RequestAttribute::GROUP_ID => ['required', 'int'],
			RequestAttribute::USER_ID_ATTRIBUTE => ['required', 'int'],
			RequestAttribute::ROLE_ATTRIBUTE => ['sometimes', new Enum(UserGroupRole::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->user_group = UserGroup::with('users')->findOrFail($values[RequestAttribute::GROUP_ID]);
		$this->user2 = User::findOrFail($values[RequestAttribute::USER_ID_ATTRIBUTE]);
		$this->role = UserGroupRole::from($values[RequestAttribute::ROLE_ATTRIBUTE] ?? UserGroupRole::MEMBER->value);
	}
}
