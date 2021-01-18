<?php

namespace App\Actions\Settings;

use AccessControl;
use App\Exceptions\JsonError;
use App\Legacy\Legacy;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Login
{
	public function do(Request $request)
	{
		$oldPassword = $request->has('oldPassword') ? $request['oldPassword'] : '';
		$oldUsername = $request->has('oldUsername') ? $request['oldUsername'] : '';

		if (Legacy::SetPassword($request)) {
			return true;
		}

		// > 4.0.8
		$adminUser = User::find(0);
		if ($adminUser->password === '' && $adminUser->username === '') {
			$adminUser->username = bcrypt($request['username']);
			$adminUser->password = bcrypt($request['password']);
			$adminUser->save();
			AccessControl::login($adminUser);

			return true;
		}

		if (AccessControl::is_admin()) {
			if ($adminUser->password === '' || Hash::check($oldPassword, $adminUser->password)) {
				$adminUser->username = bcrypt($request['username']);
				$adminUser->password = bcrypt($request['password']);
				$adminUser->save();
				unset($adminUser);

				return true;
			}
			unset($adminUser);

			throw new JsonError('Current password entered incorrectly!');
		}

		// is this necessary ?
		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();

			// this is probably sensitive to timing attacks...
			$user = User::findOrFail($id);

			if ($user->lock) {
				Logs::notice(__METHOD__, __LINE__, 'Locked user (' . $user->username . ') tried to change his identity from ' . $request->ip());
				throw new JsonError('Locked account!');
			}

			if (User::where('username', '=', $request['username'])->where('id', '!=', $id)->count()) {
				Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change his identity to ' . $request['username'] . ' from ' . $request->ip());

				throw new JsonError('Username already exists.');
			}

			if ($user->username == $oldUsername && Hash::check($oldPassword, $user->password)) {
				Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') changed his identity for (' . $request['username'] . ') from ' . $request->ip());

				$user->username = $request['username'];
				$user->password = bcrypt($request['password']);

				return $user->save();
			}
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change his identity from ' . $request->ip());

			throw new JsonError('Old username or password entered incorrectly!');
		}

		return false;
	}
}
