<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Users\AddUserRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\ListUsersRequest;
use App\Http\Requests\Users\SetUserSettingsRequest;
use App\Http\Resources\Models\UserManagementResource;
use App\Models\User;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
	/**
	 * @return ResourceCollection<UserManagementResource>
	 *
	 * @throws QueryBuilderException
	 */
	public function list(ListUsersRequest $request): ResourceCollection
	{
		return UserManagementResource::collection(User::query()->whereNot('id', '=', Auth::id())->get());
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param SetUserSettingsRequest $request
	 * @param Save                   $save
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
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
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 * @throws InvalidFormatException
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
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function create(AddUserRequest $request, Create $create): UserManagementResource
	{
		$user = $create->do($request->username(), $request->password(), $request->mayUpload(), $request->mayEditOwnSettings());

		return UserManagementResource::make($user)->setStatus(201);
	}
}
