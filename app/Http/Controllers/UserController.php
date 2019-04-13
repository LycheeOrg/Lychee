<?php

namespace App\Http\Controllers;


use App\Logs;
use App\Response;
use App\User;
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



	public function save(Request $request)
	{

		$request->validate([
			'id'       => 'required',
			'username' => 'required|string|max:100',
			'upload'   => 'required',
			'lock'     => 'required'
		]);

		$user = User::find($request['id']);
		if ($user === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified user '.$request['id']);
			return 'false';
		}

		$user->username = $request['username'];
		$user->upload = ($request['upload'] == '1');
		$user->lock = ($request['lock'] == '1');
		if ($request->has('password') && $request['password'] != '') {
			$user->password = bcrypt($request['password']);
		}

		return $user->save() ? 'true' : 'false';
	}



	public function delete(Request $request)
	{

		$request->validate([
			'id' => 'required'
		]);

		$user = User::find($request['id']);
		if ($user === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified user '.$request['id']);
			return 'false';
		}

		return $user->delete() ? 'true' : 'false';
	}



	public function create(Request $request)
	{

		$request->validate([
			'username' => 'required|string|max:100',
			'password' => 'required|string|max:50',
			'upload'   => 'required',
			'lock'     => 'required'
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