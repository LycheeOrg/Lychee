<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\UserGroup;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Facades\Gate;

class ListUserGroupRequest extends AbstractEmptyRequest
{
	public function authorize(): bool
	{
		return Gate::check(UserGroupPolicy::CAN_LIST, [UserGroup::class]);
	}
}
