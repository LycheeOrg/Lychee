<?php

namespace App\Http\Controllers;

use App\Logs;
use App\Response;
use App\User;
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
		$users = User::all();

		return $users;
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function save(Request $request)
	{
		$request->validate([
			'id' => 'required',
			'username' => 'required|string|max:100',
			'upload' => 'required',
			'lock' => 'required',
		]);

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
	public function delete(Request $request)
	{
		$request->validate([
			'id' => 'required',
		]);

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
