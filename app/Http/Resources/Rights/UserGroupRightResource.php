<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Rights;

use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserGroupRightResource extends Data
{
	public bool $can_edit;
	public bool $can_manage;

	public function __construct(UserGroup $user_group)
	{
		$this->can_edit = Gate::check(UserGroupPolicy::CAN_EDIT, [UserGroup::class, $user_group]);
		$this->can_manage = Gate::check(UserGroupPolicy::CAN_ADD_OR_REMOVE_USER, [UserGroup::class, $user_group]);
	}
}
