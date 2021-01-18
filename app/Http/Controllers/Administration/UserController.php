<?php

namespace App\Http\Controllers\Administration;

use App\Actions\User\Create;
use App\Actions\User\Save;
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
}
