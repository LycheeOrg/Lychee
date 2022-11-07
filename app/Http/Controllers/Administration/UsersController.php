<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\DTO\UserWithCapabilitiesDTO;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Users\AddUserRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\ListUsersRequest;
use App\Http\Requests\Users\SetUserSettingsRequest;
use App\Models\User;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class UsersController extends Controller
{
	/**
	 * @return Collection<UserWithCapabilitiesDTO>
	 *
	 * @throws QueryBuilderException
	 */
	public function list(ListUsersRequest $request): Collection
	{
		// PHPStan does not understand that `get` returns `Collection<User>`, but assumes that it returns `Collection<Model>`
		// @phpstan-ignore-next-line
		return User::query()->where('id', '>', 0)->get()->map(fn ($u) => UserWithCapabilitiesDTO::ofUser($u));
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
		$request->user2()->delete();
	}

	/**
	 * Create a new user.
	 *
	 * @param AddUserRequest $request
	 * @param Create         $create
	 *
	 * @return User
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function create(AddUserRequest $request, Create $create): User
	{
		return $create->do($request->username(), $request->password(), $request->mayUpload(), $request->mayEditOwnSettings());
	}
}
