<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthorizedException;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddUserRequest;
use App\Http\Requests\User\SetEmailRequest;
use App\Http\Requests\User\SetUserSettingsRequest;
use App\Models\User;
use App\Rules\IntegerIDRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as IlluminateRequest;

class UserController extends Controller
{
	/**
	 * @return Collection<User>
	 *
	 * @throws UnauthorizedException
	 */
	public function list(): Collection
	{
		// TODO: Add a comment why we want this check.
		// IMHO, this check does not make much sense.
		// Why is the privilege to upload photos a sufficient condition to
		// see the list of users?
		// IMHO, these privileges should be unrelated.
		// If we only wanted to grant the privilege to admins, then we could
		// simply remove this check here and change the middleware in
		// `routes/admin.php`.
		$user = AccessControl::user();
		if (!AccessControl::is_admin() && !$user->upload) {
			throw new UnauthorizedException();
		}

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
	 * @throws ModelNotFoundException
	 */
	public function save(SetUserSettingsRequest $request, Save $save): void
	{
		/** @var User $user */
		$user = User::query()->findOrFail($request->userID());
		$save->do($user, $request->username(), $request->password(), $request->mayUpload(), $request->isLocked());
	}

	/**
	 * Deletes a user.
	 * FIXME: What happen to the albums owned ?
	 *
	 * @param IlluminateRequest $request
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 */
	public function delete(IlluminateRequest $request): void
	{
		$validated = $request->validate([
			'id' => ['required', new IntegerIDRule(false)],
		]);

		/** @var User $user */
		$user = User::query()->findOrFail($validated['id']);
		$user->delete();
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
	 * @throws ModelDBException
	 * @throws QueryBuilderException
	 */
	public function updateEmail(SetEmailRequest $request): void
	{
		$user = AccessControl::user();
		$user->email = $request->email();

		if (is_null($request->email())) {
			$user->notifications()->delete();
		}

		$user->save();
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
