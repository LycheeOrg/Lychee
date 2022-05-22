<?php

namespace App\ModelFunctions;

use App\Contracts\LycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\UnauthenticatedException;
use App\LDAP\FixedArray;
use App\LDAP\LDAPActions;
use App\LDAP\LDAPFunctions;
use App\Legacy\Legacy;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SessionFunctions
{
	public ?User $user_data = null;

	/* ldap server access */
	protected ?LDAPFunctions $ldap = null;

	public function log_as_id($id): void
	{
		Session::put('login', true);
		Session::put('UserID', $id);
	}

	/**
	 * Return true if the user is logged in (Admin or User)
	 * Return false if it is Guest access.
	 *
	 * @return bool
	 */
	public function is_logged_in(): bool
	{
		return Session::get('login') === true;
	}

	/**
	 * Return true if the user is logged in and an admin.
	 *
	 * @return bool
	 */
	public function is_admin(): bool
	{
		return Session::get('login') && Session::get('UserID') === 0;
	}

	/**
	 * @throws UnauthenticatedException
	 */
	public function can_upload(): bool
	{
		return $this->is_logged_in() && ($this->id() == 0 || $this->user()->may_upload);
	}

	/**
	 * Return the current ID of the user
	 * what happens when UserID is not set? :p.
	 *
	 * @return int
	 *
	 * @throws UnauthenticatedException
	 */
	public function id(): int
	{
		if (!Session::get('login')) {
			throw new UnauthenticatedException();
		}
		$uid = Session::get('UserID');
		if (is_null($uid)) {
			$this->logout();
			throw new UnauthenticatedException();
		}

		return $uid;
	}

	/**
	 * Return User object given a positive ID.
	 *
	 * @throws UnauthenticatedException
	 */
	private function accessUserData(): User
	{
		$id = $this->id();
		$this->user_data = User::query()->find($id);

		return $this->user_data;
	}

	/**
	 * Return User object and cache the result.
	 *
	 * @throws UnauthenticatedException
	 */
	public function user(): User
	{
		return $this->user_data ?? $this->accessUserData();
	}

	/**
	 * Return true if the currently logged-in user is the one provided
	 * (or if that user is Admin).
	 *
	 * @param int userId
	 *
	 * @return bool
	 */
	public function is_current_user_or_admin(int $userId): bool
	{
		return Session::get('login') && (Session::get('UserID') === $userId || Session::get('UserID') === 0);
	}

	/**
	 * Given a user, login.
	 */
	public function login(User $user): void
	{
		$this->user_data = $user;
		Session::put('login', true);
		Session::put('UserID', $user->id);
	}

	/**
	 * Sets the session values when no there is no username and password in the database.
	 *
	 * @return bool returns true when no login was found
	 */
	public function noLogin(): bool
	{
		/** @var User $adminUser */
		$adminUser = User::query()->find(0);
		if ($adminUser !== null && $adminUser->password === '' && $adminUser->username === '') {
			$this->user_data = $adminUser;
			Session::put('login', true);
			Session::put('UserID', 0);

			return true;
		}

		return Legacy::noLogin();
	}

	/**
	 * Given a username, password and ip (for logging), try to log the user.
	 * Returns true if succeeded, false if failed.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 *
	 * @throws LycheeException
	 */
	public function log_as_user(string $username, string $password, string $ip): bool
	{
		if (Configs::get_value('ldap_enabled', '0')) {
			return $this->log_with_ldap($username, $password, $ip);
		}

		// We select the NON ADMIN user
		/** @var User $user */
		$user = User::query()->where('username', '=', $username)->where('id', '>', '0')->first();

		if ($user != null && Hash::check($password, $user->password)) {
			$this->user_data = $user;
			Session::put('login', true);
			Session::put('UserID', $user->id);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

			return true;
		}

		return false;
	}

	/**
	 * Given a username and Password authenticate against LDAP.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 *
	 * @throws LycheeException
	 */
	public function log_with_ldap(string $username, string $password, string $ip): bool
	{
		try {
			if (empty($this->ldap)) {
				$this->ldap = new LDAPFunctions();
			}

			$valid = $this->ldap->check_pass($username, $password);
			if (!$valid) {
				return false;
			}

			/** @var FixedArray $ldapUserData */
			$ldapUserData = $this->ldap->get_user_data($username);
			if ($ldapUserData == null) {
				// Should never happen if the ldap server is functioning correctly
				// @codeCoverageIgnoreStart
				return false;
				// @codeCoverageIgnoreEnd
			}
			/** @var User $user */
			$user = User::query()->where('username', '=', $username)->first();
			if ($user == null) {
				LDAPActions::create_user_not_exist($username, $ldapUserData);
				$user = User::query()->where('username', '=', $username)->first();
			}
			if ($user !== null) {
				// admin user cannot be used with LDAP
				if ($user->id == 0) {
					return false;
				}
				$this->user_data = $user;
				Session::put('login', true);
				Session::put('UserID', $user->id);
				LDAPActions::update_user($username, $ldapUserData);

				Logs::notice(__METHOD__, __LINE__, sprintf('User (%s) has logged in from %s', $username, $ip));

				return true;
			}
			// Can only happen if the user cannot be created in the database
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Given a username, password and ip (for logging), try to log the user as admin.
	 * Returns true if succeeded, false if failed.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function log_as_admin(string $username, string $password, string $ip): bool
	{
		/** @var User $adminUser */
		$adminUser = User::query()->find(0);

		if ($adminUser !== null) {
			// Admin User exist, so we check against it.
			if (Hash::check($username, $adminUser->username) && Hash::check($password, $adminUser->password)) {
				$this->user_data = $adminUser;
				Session::put('login', true);
				Session::put('UserID', 0);
				Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

				return true;
			}

			return false;
		}
		// Admin User does not exist yet, so we use the Legacy.

		return Legacy::log_as_admin($username, $password, $ip);
	}

	/**
	 * Log out the current user.
	 */
	public function logout()
	{
		$this->user_data = null;
		Session::flush();
	}
}
