<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserGroup\GetUserGroupRequest;
use App\Http\Requests\UserGroup\ManageUserGroupRequest;
use App\Http\Resources\Models\UserGroupResource;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for managing users within groups.
 */
class UserGroupsManagementController extends Controller
{
	public function get(GetUserGroupRequest $request): UserGroupResource
	{
		return new UserGroupResource($request->user_group());
	}

	public function addUser(ManageUserGroupRequest $request): UserGroupResource
	{
		$request->user_group()->users()->attach($request->user2()->id, ['role' => $request->role()->value]);

		return new UserGroupResource($request->user_group());
	}

	public function removeUser(ManageUserGroupRequest $request): UserGroupResource
	{
		$request->user_group()->users()->detach($request->user2()->id);

		return new UserGroupResource($request->user_group());
	}

	public function updateUserRole(ManageUserGroupRequest $request): UserGroupResource
	{
		$request->user_group()->users()->updateExistingPivot($request->user2()->id, ['role' => $request->role()->value]);
		$request->user_group()->load('users');

		return new UserGroupResource($request->user_group());
	}
}
