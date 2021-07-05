<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\UserPostIdRequest;
use App\Http\Requests\UserRequests\UserPostRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function list()
	{
		return User::where('id', '>', 0)->get();
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param UserPostRequest $request
	 *
	 * @return string
	 */
	public function save(UserPostRequest $request, Save $save)
	{
		$user = User::findOrFail($request['id']);

		return $save->do($user, $request->all()) ? 'true' : 'false';
	}

	/**
	 * Delete a user.
	 * FIXME: What happen to the albums owned ?
	 *
	 * @param UserPostIdRequest $request
	 *
	 * @return string
	 */
	public function delete(UserPostIdRequest $request)
	{
		$user = User::findOrFail($request['id']);

		return $user->delete() ? 'true' : 'false';
	}

	/**
	 * Create a new user.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function create(Request $request, Create $create)
	{
		$data = $request->validate([
			'username' => 'required|string|max:100',
			'password' => 'required|string|max:50',
			'upload' => 'required',
			'lock' => 'required',
		]);

		return $create->do($data) ? 'true' : 'false';
	}

	/**
	 * Update the email of a user.
	 * Will delete all notifications if the email is left empty.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function updateEmail(Request $request, Save $save)
	{
		if ($request->email != '') {
			$request->validate([
				'email' => 'email|max:100',
			]);
		}

		$id = AccessControl::id();
		$user = User::findOrFail($id);

		$user->email = $request->email;

		if ($request->email = '') {
			$user->notifications()->delete();
		}

		return $user->save() ? 'true' : 'false';
	}

	/**
	 * Return the email address of a user.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		$id = AccessControl::id();
		$user = User::findOrFail($id);

		if ($user->email) {
			return json_encode($user->email);
		} else {
			return json_encode('');
		}
	}
}
