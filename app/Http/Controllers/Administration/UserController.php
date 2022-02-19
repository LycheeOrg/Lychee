<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\SetEmailRequest;
use App\Http\Requests\User\SetUserSettingsRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
	/**
	 * @return Collection<User>
	 *
	 * @throws QueryBuilderException
	 */
	public function list(): Collection
	{
		return User::query()->where('id', '>', 0)->get();
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
			$request->isLocked()
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
		return $create->do($request->username(), $request->password(), $request->mayUpload(), $request->isLocked());
	}

	/**
	 * Updates the email address of the currently authenticated user.
	 * Deletes all notifications if the email address is empty.
	 *
	 * TODO: Why is this an independent request? IMHO this should be combined with the other user settings.
	 *
	 * @param SetEmailRequest $request
	 *
	 * @return void
	 *
	 * @throws InternalLycheeException
	 * @throws ModelDBException
	 */
	public function setEmail(SetEmailRequest $request): void
	{
		try {
			$user = AccessControl::user();
			$user->email = $request->email();

			if ($request->email() === null) {
				$user->notifications()->delete();
			}

			$user->save();
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Laravel\'s notification module', $e);
		}
	}

	/**
	 * Returns the email address of the currently authenticated user.
	 *
	 * TODO: Why is this an independent request? IMHO this should be combined with the GET request for the other user settings (see session init)
	 *
	 * @return array{email: ?string}
	 */
	public function getEmail(): array
	{
		return [
			'email' => AccessControl::user()->email,
		];
	}
}
