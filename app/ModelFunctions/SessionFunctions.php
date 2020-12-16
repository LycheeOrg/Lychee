<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App;
use App\Exceptions\NotLoggedInException;
use App\Legacy\Legacy;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SessionFunctions
{
	public function log_as_id($id)
	{
		if (App::runningUnitTests()) {
			Session::put('login', true);
			Session::put('UserID', $id);
		}
	}

	/**
	 * Return true if the user is logged in (Admin or User)
	 * Return false if it is Guest access.
	 *
	 * @return bool
	 */
	public function is_logged_in()
	{
		if (Session::get('login') === true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return true if the user is logged in and an admin.
	 *
	 * @return bool
	 */
	public function is_admin()
	{
		return Session::get('login') && Session::get('UserID') === 0;
	}

	public function can_upload(): bool
	{
		return $this->id() == 0 || $this->user()->upload;
	}

	/**
	 * Return the current ID of the user
	 * what happens when UserID is not set? :p.
	 *
	 * @return int
	 */
	public function id()
	{
		if (!Session::get('login')) {
			throw new NotLoggedInException();
		}

		return Session::get('UserID');
	}

	/**
	 * Return User object given a positive ID.
	 */
	private function accessUserData(): User
	{
		$id = $this->id();
		$this->user_data = User::find($id);

		return $this->user_data;
	}

	/**
	 * Return User object and cache the result.
	 */
	public function user(): User
	{
		return $this->user_data ?? $this->accessUserData();
	}

	/**
	 * Return true if the currently logged in user is the one provided
	 * (or if that user is Admin).
	 *
	 * @param int userId
	 *
	 * @return bool
	 */
	public function is_current_user(int $userId)
	{
		return Session::get('login') && (Session::get('UserID') === $userId || Session::get('UserID') === 0);
	}

	/**
	 * Given a user, login.
	 */
	public function login(User $user)
	{
		Session::put('login', true);
		Session::put('UserID', $user->id);
	}

	/**
	 * Sets the session values when no there is no username and password in the database.
	 *
	 * @return bool returns true when no login was found
	 */
	public function noLogin()
	{
		$adminUser = User::find(0);
		if ($adminUser->password === '' && $adminUser->username === '') {
			Session::put('login', true);
			Session::put('UserID', 0);

			return true;
		}

		return Legacy::noLogin();

		return false;
	}

	/**
	 * Given a username, password and ip (for logging), try to log the user.
	 * returns true if succeed
	 * returns false if fail.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function log_as_user(string $username, string $password, string $ip)
	{
		// We select the NON ADMIN user
		$user = User::where('username', '=', $username)->where('id', '>', '0')->first();

		if ($user != null && Hash::check($password, $user->password)) {
			Session::put('login', true);
			Session::put('UserID', $user->id);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

			return true;
		}

		return false;
	}

	/**
	 * Given a username, password and ip (for logging), try to log the user as admin.
	 * returns true if succeed
	 * returns false if fail.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function log_as_admin(string $username, string $password, string $ip)
	{
		$AdminUser = User::find(0);
		if (Hash::check($username, $AdminUser->username) && Hash::check($password, $AdminUser->password)) {
			Session::put('login', true);
			Session::put('UserID', 0);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

			return true;
		}

		return Legacy::log_as_admin($username, $password, $ip);

		return false;
	}

	/**
	 * Given an albumID, check if it exists in the visible_albums session variable.
	 *
	 * @param $albumID
	 *
	 * @return bool
	 */
	public function has_visible_album($albumID)
	{
		if (!Session::has('visible_albums')) {
			return false;
		}

		$visible_albums = Session::get('visible_albums');
		$visible_albums = explode('|', $visible_albums);

		return in_array($albumID, $visible_albums);
	}

	/**
	 * Add new album to the visible_albums session variable.
	 *
	 * @param $albumIDs
	 */
	public function add_visible_albums($albumIDs)
	{
		if (Session::has('visible_albums')) {
			$visible_albums = Session::get('visible_albums');
		} else {
			$visible_albums = '';
		}

		$visible_albums = explode('|', $visible_albums);
		foreach ($albumIDs as $albumID) {
			if (!in_array($albumID, $visible_albums)) {
				$visible_albums[] = $albumID;
			}
		}

		$visible_albums = implode('|', $visible_albums);
		Session::put('visible_albums', $visible_albums);
	}

	/**
	 * Log out the current user.
	 */
	public function logout()
	{
		Session::flush();
	}
}
