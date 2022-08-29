<?php

namespace App\Http\Controllers;

use App\Contracts\LycheeException;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Facades\Helpers;
use App\Facades\Lang;
use App\Http\Requests\Session\LoginRequest;
use App\Legacy\AdminAuthentication;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
	private ConfigFunctions $configFunctions;
	private GitHubFunctions $gitHubFunctions;

	/**
	 * @param ConfigFunctions $configFunctions
	 * @param GitHubFunctions $gitHubFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions, GitHubFunctions $gitHubFunctions)
	{
		$this->configFunctions = $configFunctions;
		$this->gitHubFunctions = $gitHubFunctions;
	}

	/**
	 * First function being called via AJAX.
	 *
	 * TODO: Remove attribute `status`.
	 * TODO: Add nullable attribute `user` with a proper user object.
	 * TODO: Merge attributes `is_admin`, `may_upload`, `username`, and `is_locked` into user object.
	 *
	 * `status === 0 ` (i.e. "no config") is legacy and does not occur.
	 *
	 * `status === {1|2}` indicates whether a user is authenticated or not.
	 * But we should return a nullable attribute `user` which either holds the
	 * currently authenticated user object or `null` if no user is
	 * authenticated.
	 *
	 * The user-related attributes (`is_admin`, etc.) should be part of that
	 * user object.
	 *
	 * @return array
	 *
	 * @throws LycheeException
	 */
	public function init(): array
	{
		try {
			// Return settings
			$return = [];

			// Check if login credentials exist and login if they don't
			if (Auth::check() || AdminAuthentication::loginAsAdminIfNotRegistered()) {
				if (Gate::check(UserPolicy::IS_ADMIN)) {
					$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');
					$return['admin'] = true;
					$return['may_upload'] = true; // not necessary
					$return['config'] = $this->configFunctions->admin();
					$return['config']['location'] = base_path('public/');
				} else {
					/** @var User $user */
					$user = Auth::user() ?? throw new UnauthenticatedException();

					$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');
					$return['config'] = $this->configFunctions->public();
					$return['is_locked'] = $user->is_locked;   // may user change their password?
					$return['may_upload'] = $user->may_upload; // may user upload?
					$return['username'] = $user->username;
				}

				// here we say whether we logged in because there is no login/password or if we actually entered a login/password
				// TODO: Refactor this. At least, rename the flag `login` to something more understandable, like `isAdminUserConfigured`, but rather re-factor the whole logic, i.e. creating the initial user should be part of the installation routine.
				$return['config']['login'] = !AdminAuthentication::isAdminNotRegistered();
				$return['config']['lang_available'] = Lang::get_lang_available();
			} else {
				// Logged out
				$return['config'] = $this->configFunctions->public();
				if (Configs::getValueAsBool('hide_version_number')) {
					$return['config']['version'] = '';
				}
				$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDOUT');
			}

			// Consolidate sorting attributes
			$return['config']['sorting_albums'] = AlbumSortingCriterion::createDefault()->toArray();
			$return['config']['sorting_photos'] = PhotoSortingCriterion::createDefault()->toArray();
			unset($return['config']['sorting_albums_col']);
			unset($return['config']['sorting_albums_order']);
			unset($return['config']['sorting_photos_col']);
			unset($return['config']['sorting_photos_order']);

			// Device dependent settings
			$deviceType = Helpers::getDeviceType();
			// UI behaviour needs to be slightly modified if client is a TV
			$return['config_device'] = $this->configFunctions->get_config_device($deviceType);

			// we also return the local
			$return['locale'] = Lang::get_lang();

			$return['update_json'] = 0;
			$return['update_available'] = false;

			return array_merge($return, $this->gitHubFunctions->checkUpdates());
		} catch (BindingResolutionException) {
			throw new FrameworkException('Laravel\'s path component');
		}
	}

	/**
	 * Login tentative.
	 *
	 * @param LoginRequest $request
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 */
	public function login(LoginRequest $request): void
	{
		// No login
		if (AdminAuthentication::loginAsAdminIfNotRegistered()) {
			Logs::warning(__METHOD__, __LINE__, 'DEFAULT LOGIN!');

			return;
		}

		if (AdminAuthentication::loginAsAdmin($request->username(), $request->password(), $request->ip())) {
			return;
		}

		if (Auth::attempt(['username' => $request->username(), 'password' => $request->password()])) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $request->username() . ') has logged in from ' . $request->ip());

			return;
		}

		// TODO: We could avoid this separate log entry and let the exeption handler to all the logging, if we would add "context" (see Laravel docs) to those exceptions which need it.
		Logs::error(__METHOD__, __LINE__, 'User (' . $request->username() . ') has tried to log in from ' . $request->ip());

		throw new UnauthenticatedException('Unknown user or invalid password');
	}

	/**
	 * Unsets the session values.
	 *
	 * @return void
	 */
	public function logout(): void
	{
		Auth::logout();
		Session::flush();
	}
}
