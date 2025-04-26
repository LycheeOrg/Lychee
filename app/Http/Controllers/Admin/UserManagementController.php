<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Statistics\Spaces;
use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Enum\CacheTag;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\UserManagement\AddUserRequest;
use App\Http\Requests\UserManagement\DeleteUserRequest;
use App\Http\Requests\UserManagement\ManagmentListUsersRequest;
use App\Http\Requests\UserManagement\SetUserSettingsRequest;
use App\Http\Resources\Models\UserManagementResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Controller responsible for user management.
 */
class UserManagementController extends Controller
{
	/**
	 * Get the list of users for management purposes.
	 *
	 * @return Collection<array-key, UserManagementResource>
	 */
	public function list(ManagmentListUsersRequest $request, Spaces $spaces): Collection
	{
		/** @var Collection<int,User> $users */
		$users = User::select(['id', 'username', 'may_administrate', 'may_upload', 'may_edit_own_settings', 'quota_kb', 'description', 'note'])->orderBy('id', 'asc')->get();
		$spaces_per_user = $spaces->getFullSpacePerUser();
		$zipped = $users->zip($spaces_per_user);

		return $zipped->map(fn ($item) => new UserManagementResource($item[0], $item[1], $request->is_se()));
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 */
	public function save(SetUserSettingsRequest $request, Save $save): void
	{
		$save->do(
			user: $request->user2(),
			username: $request->username(),
			password: $request->password(),
			may_upload: $request->mayUpload(),
			may_edit_own_settings: $request->mayEditOwnSettings(),
			quota_kb: $request->quota_kb(),
			note: $request->note()
		);

		TaggedRouteCacheUpdated::dispatch(CacheTag::USERS);
	}

	/**
	 * Deletes a user.
	 *
	 * The albums and photos owned by the user are re-assigned to the
	 * admin user.
	 */
	public function delete(DeleteUserRequest $request): void
	{
		if ($request->user2()->id === Auth::id()) {
			throw new UnauthorizedException('You are not allowed to delete yourself');
		}
		$request->user2()->delete();

		TaggedRouteCacheUpdated::dispatch(CacheTag::USERS);
	}

	/**
	 * Create a new user.
	 */
	public function create(AddUserRequest $request, Create $create): UserManagementResource
	{
		$user = $create->do(
			username: $request->username(),
			password: $request->password(),
			may_upload: $request->mayUpload(),
			may_edit_own_settings: $request->mayEditOwnSettings(),
			quota_kb: $request->quota_kb(),
			note: $request->note()
		);

		TaggedRouteCacheUpdated::dispatch(CacheTag::USERS);

		return new UserManagementResource($user, ['id' => $user->id, 'size' => 0], $request->is_se());
	}
}