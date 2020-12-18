<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\UserPostIdRequest;
use App\Http\Requests\UserRequests\UserPostRequest;
use App\Models\Logs;
use App\Models\User;
use App\Response;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware([]);
	}

	public function list()
	{
		return User::where('id', '>', 0)->get();
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function save(UserPostRequest $request)
	{
		$user = User::find($request['id']);
		if ($user === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified user ' . $request['id']);

			return 'false';
		}

		if (User::where('username', '=', $request['username'])->where('id', '!=', $request['id'])->count()) {
			return Response::error('username must be unique');
		}

		// check for duplicate name here !
		$user->username = $request['username'];
		$user->upload = ($request['upload'] == '1');
		$user->lock = ($request['lock'] == '1');
		if ($request->has('password') && $request['password'] != '') {
			$user->password = bcrypt($request['password']);
		}

		return $user->save() ? 'true' : 'false';
	}

	/**
	 * Delete a user.
	 * FIXME: What happen to the albums owned ?
	 *
	 * @param Request $request
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function delete(UserPostIdRequest $request)
	{
		$user = User::find($request['id']);
		if ($user === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified user ' . $request['id']);

			return 'false';
		}

		return $user->delete() ? 'true' : 'false';
	}

	/**
	 * Create a new user.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function create(Request $request)
	{
		$request->validate([
			'username' => 'required|string|max:100',
			'password' => 'required|string|max:50',
			'upload' => 'required',
			'lock' => 'required',
		]);

		if (User::where('username', '=', $request['username'])->count()) {
			return Response::error('username must be unique');
		}

		$user = new User();
		$user->upload = ($request['upload'] == '1');
		$user->lock = ($request['lock'] == '1');
		$user->username = $request['username'];
		$user->password = bcrypt($request['password']);

		return @$user->save() ? 'true' : 'false';
	}
}
