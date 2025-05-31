<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserGroup\CreateUserGroupRequest;
use App\Http\Requests\UserGroup\DeleteUserGroupRequest;
use App\Http\Requests\UserGroup\ListUserGroupRequest;
use App\Http\Requests\UserGroup\UpdateUserGroupRequest;
use App\Models\UserGroup;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

/**
 * Controller responsible for user management.
 */
class UserGroupsController extends Controller
{
	public function list(ListUserGroupRequest $request): array
	{
		return UserGroup::all()->toArray();
	}

	public function create(CreateUserGroupRequest $request): UserGroup
	{
		$this->validateUniqueGroupName($request->name());

		return UserGroup::create([
			'name' => $request->name(),
			'description' => $request->description(),
		]);
	}

	public function update(UpdateUserGroupRequest $request): UserGroup
	{
		$this->validateUniqueGroupName($request->name(), $request->user_group()->id);

		$request->user_group()->update([
			'name' => $request->name(),
			'description' => $request->description(),
		]);

		return $request->user_group();
	}

	public function delete(DeleteUserGroupRequest $request): void
	{
		$request->user_group()->delete();
	}

	private function validateUniqueGroupName(string $name, ?int $exclude_id = null): void
	{
		$query = UserGroup::where('name', $name)
			->when($exclude_id !== null, fn ($q) => $q->where('id', '!=', $exclude_id));

		if ($query->exists()) {
			throw ValidationException::withMessages(['name' => 'A group with this name already exists.']);
		}
	}
}