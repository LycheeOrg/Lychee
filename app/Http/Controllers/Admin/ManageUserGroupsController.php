<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserGroup\GetUserGroupRequest;
use App\Http\Requests\UserGroup\ManageUserGroupRequest;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for managing users within groups.
 */
class ManageUserGroupsController extends Controller
{
	public function get(GetUserGroupRequest $request): array
	{
		return $request->user_group()->users()->toArray();
	}

	public function addUser(ManageUserGroupRequest $request): void
	{
		$request->userGroup()->users()->attach($request->user2()->id, ['role' => $request->role()->value]);
	}

	public function removeUser(ManageUserGroupRequest $request): void
	{
		$request->userGroup()->users()->detach($request->user2()->id);
	}

	public function updateUserRole(ManageUserGroupRequest $request): void
	{
		$request->userGroup()->users()->updateExistingPivot($request->user2()->id, ['role' => $request->role()->value]);
	}
}
