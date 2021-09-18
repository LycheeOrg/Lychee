<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\UserPostIdRequest;
use App\Http\Requests\UserRequests\UserPostRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response as IlluminateResponse;

class UserController extends Controller
{
	public function list(): Collection
	{
		return User::query()->where('id', '>', 0)->get();
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param UserPostRequest $request
	 * @param Save            $save
	 *
	 * @return User
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 */
	public function save(UserPostRequest $request, Save $save): User
	{
		/** @var User $user */
		$user = User::query()->findOrFail($request['id']);

		return $save->do($user, $request->all());
	}

	/**
	 * Delete a user.
	 * FIXME: What happen to the albums owned ?
	 *
	 * @param UserPostIdRequest $request
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 */
	public function delete(UserPostIdRequest $request): void
	{
		$user = User::query()->findOrFail($request['id']);

		try {
			$success = $user->delete();
		} catch (\Throwable $e) {
			throw ModelDBException::create('user', 'delete', $e);
		}
		if (!$success) {
			throw ModelDBException::create('user', 'delete');
		}
	}

	/**
	 * Create a new user.
	 *
	 * @param IlluminateRequest $request
	 * @param Create            $create
	 *
	 * @return User
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function create(IlluminateRequest $request, Create $create): User
	{
		$data = $request->validate([
			'username' => 'required|string|max:100',
			'password' => 'required|string|max:50',
			'upload' => 'required',
			'lock' => 'required',
		]);

		return $create->do($data);
	}

	/**
	 * Update the email of a user.
	 * Will delete all notifications if the email is left empty.
	 *
	 * @param IlluminateRequest $request
	 *
	 * @return IlluminateResponse
	 */
	public function updateEmail(IlluminateRequest $request): IlluminateResponse
	{
		if ($request->email != '') {
			$request->validate([
				'email' => 'email|max:100',
			]);
		}

		$user = AccessControl::user();

		$user->email = $request->email;

		if (is_null($request->email)) {
			$user->notifications()->delete();
		}

		return $user->save() ? response()->noContent() : response('', 500);
	}

	/**
	 * Return the email address of a user.
	 *
	 * @return string
	 */
	public function getEmail(): string
	{
		$user = AccessControl::user();

		if ($user->email) {
			return json_encode($user->email);
		} else {
			return json_encode('');
		}
	}
}
