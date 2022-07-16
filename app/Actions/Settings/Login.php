<?php

namespace App\Actions\Settings;

use App\Auth\Authorization;
use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Login
{
	/**
	 * Changes and modifies logins in multiple ways.
	 *
	 * TODO: This method requires thorough refactoring, because it is a "god" method.
	 *
	 * This method serves three different purposes and the method tries to
	 * "cleverly" find out which use-case is needed:
	 *
	 *  1. Initially setting the admin password in case no password has
	 *     been set yet (e.g. after/during installation)
	 *  2. Changing a user's own password by the users themselves.
	 *  3. Changing a user's username and password for some arbitrary user
	 *     by the administrator.
	 *
	 * Unfortunately, this makes it rather difficult to authorize the request
	 * beforehand (see similar remark in
	 * {@link \App\Http\Requests\Settings\ChangeLoginRequest::authorize()}).
	 * Hence, this method is also responsible to authorize the various
	 * use-cases properly.
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 * @throws ModelNotFoundException
	 * @throws InvalidPropertyException
	 * @throws InvalidConfigOption
	 * @throws UnauthorizedException
	 */
	public function do(string $username, string $password, ?string $oldPassword, string $ip): void
	{
		try {
			$hashedUsername = bcrypt($username);
			$hashedPassword = bcrypt($password);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Could not hash username or password', $e);
		}

		if ($this->updateAsAdmin($hashedUsername, $hashedPassword, $oldPassword)) {
			return;
		}
		$this->updateAsUser($username, $hashedPassword, $oldPassword, $ip);
	}

	/**
	 * Given a hashed username and password, update the Admin credentials.
	 *
	 * @param string  $hashedUsername
	 * @param string  $hashedPassword
	 * @param ?string $oldPassword
	 *
	 * @return bool true if Admin credentials are updated, false otherwise
	 *
	 * @throws RuntimeException
	 * @throws ModelDBException
	 * @throws InvalidArgumentException
	 * @throws BadRequestException
	 * @throws AuthenticationException
	 * @throws UnauthenticatedException
	 */
	private function updateAsAdmin(string $hashedUsername, string $hashedPassword, ?string $oldPassword): bool
	{
		if (Authorization::isAdminNotConfigured()) {
			/** @var User $adminUser */
			$adminUser = User::query()->findOrFail(0);
			if ($adminUser->password === '' && $adminUser->username === '') {
				$adminUser->username = $hashedUsername;
				$adminUser->password = $hashedPassword;
				$adminUser->save();
				Authorization::login($adminUser);

				return true;
			}
		}

		if (Authorization::isAdmin()) {
			/** @var User $adminUser */
			$adminUser = User::query()->findOrFail(0);
			if ($adminUser->password === '' || Hash::check($oldPassword, $adminUser->password)) {
				$adminUser->username = $hashedUsername;
				$adminUser->password = $hashedPassword;
				$adminUser->save();

				return true;
			}

			throw new UnauthenticatedException('Password is invalid');
		}

		return false;
	}

	/**
	 * Updates a User password.
	 *
	 * @param string      $username       New username
	 * @param string      $hashedPassword New password (hashed)
	 * @param string|null $oldPassword    Old Password
	 * @param string      $ip             Ip address of the change
	 *
	 * @return void
	 *
	 * @throws AuthenticationException
	 * @throws UnauthorizedException
	 * @throws QueryBuilderException
	 * @throws ConflictingPropertyException
	 * @throws InvalidArgumentException
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	private function updateAsUser(string $username, string $hashedPassword, ?string $oldPassword, string $ip): void
	{
		// this is probably sensitive to timing attacks...
		/** @var User $user */
		$user = Authorization::userOrFail();

		if ($user->is_locked) {
			Logs::notice(__METHOD__, __LINE__, 'Locked user (' . $user->username . ') tried to change their identity from ' . $ip);
			throw new UnauthorizedException('Account is locked');
		}

		if (User::query()->where('username', '=', $username)->where('id', '!=', $user->id)->count() !== 0) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change their identity to ' . $username . ' from ' . $ip);
			throw new ConflictingPropertyException('Username already exists.');
		}

		if (Hash::check($oldPassword, $user->password)) {
			if ($username !== $user->username) {
				Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') changed their identity for (' . $username . ') from ' . $ip);
			}
			$user->username = $username;
			$user->password = $hashedPassword;
			$user->save();

			return;
		}
		Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change their identity from ' . $ip);

		throw new UnauthenticatedException('Previous username or password are invalid');
	}
}
