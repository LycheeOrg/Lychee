<?php

namespace App\Http\Controllers\Admin;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Users\AddUserRequest;
use App\Http\Requests\Users\CountUserRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\ManagmentListUsersRequest;
use App\Http\Requests\Users\SetUserSettingsRequest;
use App\Http\Resources\Collections\UserManagementCollectionResource;
use App\Http\Resources\Models\UserManagementResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controller responsible for the config.
 */
class UsersController extends Controller
{
	public function count(CountUserRequest $_request): int
	{
		return User::count();
	}

	public function list(ManagmentListUsersRequest $_request): UserManagementCollectionResource
	{
		return new UserManagementCollectionResource(User::where('id', '!=', Auth::id())->get());
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param SetUserSettingsRequest $request
	 * @param Save                   $save
	 *
	 * @return void
	 */
	public function save(SetUserSettingsRequest $request, Save $save): void
	{
		$save->do(
			$request->user2(),
			$request->username(),
			$request->password(),
			$request->mayUpload(),
			$request->mayEditOwnSettings()
		);
	}

	/**
	 * Deletes a user.
	 *
	 * The albums and photos owned by the user are re-assigned to the
	 * admin user.
	 *
	 * @param DeleteUserRequest $request
	 *
	 * @return void
	 */
	public function delete(DeleteUserRequest $request): void
	{
		if ($request->user2()->id === Auth::id()) {
			throw new UnauthorizedException('You are not allowed to delete yourself');
		}
		$request->user2()->delete();
	}

	/**
	 * Create a new user.
	 *
	 * @param AddUserRequest $request
	 * @param Create         $create
	 *
	 * @return UserManagementResource
	 */
	public function create(AddUserRequest $request, Create $create): UserManagementResource
	{
		$user = $create->do(
			username: $request->username(),
			password: $request->password(),
			mayUpload: $request->mayUpload(),
			mayEditOwnSettings: $request->mayEditOwnSettings());

		return new UserManagementResource($user);
	}
}