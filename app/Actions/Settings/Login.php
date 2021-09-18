<?php

namespace App\Actions\Settings;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Facades\AccessControl;
use App\Legacy\Legacy;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Login
{
	/**
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 * @throws ModelNotFoundException
	 * @throws InvalidPropertyException
	 */
	public function do(Request $request): void
	{
		$oldPassword = $request->has('oldPassword') ? $request['oldPassword'] : '';
		$oldUsername = $request->has('oldUsername') ? $request['oldUsername'] : '';

		if (Legacy::SetPassword($request)) {
			return;
		}

		// > 4.0.8
		/** @var User $adminUser */
		$adminUser = User::query()->find(0);
		if ($adminUser->password === '' && $adminUser->username === '') {
			try {
				$adminUser->username = bcrypt($request['username']);
				$adminUser->password = bcrypt($request['password']);
				$success = $adminUser->save();
			} catch (\Throwable $e) {
				throw ModelDBException::create('user', 'update', $e);
			}
			if (!$success) {
				throw ModelDBException::create('user', 'update');
			}
			AccessControl::login($adminUser);

			return;
		}

		if (AccessControl::is_admin()) {
			if ($adminUser->password === '' || Hash::check($oldPassword, $adminUser->password)) {
				try {
					$adminUser->username = bcrypt($request['username']);
					$adminUser->password = bcrypt($request['password']);
					$success = $adminUser->save();
				} catch (\Throwable $e) {
					throw ModelDBException::create('user', 'update', $e);
				}
				if (!$success) {
					throw ModelDBException::create('user', 'update');
				}
				unset($adminUser);

				return;
			}
			unset($adminUser);

			throw new UnauthenticatedException('Password is invalid');
		}

		// is this necessary ?
		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();

			// this is probably sensitive to timing attacks...
			/** @var User $user */
			$user = User::query()->findOrFail($id);

			if ($user->lock) {
				Logs::notice(__METHOD__, __LINE__, 'Locked user (' . $user->username . ') tried to change his identity from ' . $request->ip());
				throw new UnauthenticatedException('Account is locked');
			}

			if (User::query()->where('username', '=', $request['username'])->where('id', '!=', $id)->count()) {
				Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change his identity to ' . $request['username'] . ' from ' . $request->ip());

				throw new InvalidPropertyException('Username already exists.');
			}

			if ($user->username == $oldUsername && Hash::check($oldPassword, $user->password)) {
				Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') changed his identity for (' . $request['username'] . ') from ' . $request->ip());

				try {
					$user->username = $request['username'];
					$user->password = bcrypt($request['password']);
					$success = $user->save();
				} catch (\Throwable $e) {
					throw ModelDBException::create('user', 'save', $e);
				}
				if (!$success) {
					throw ModelDBException::create('user', 'save');
				}
			}
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change his identity from ' . $request->ip());

			throw new UnauthenticatedException('Previous username or password are invalid');
		}
	}
}
