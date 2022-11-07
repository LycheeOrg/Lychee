<?php

namespace App\Http\Controllers;

use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\DTO\Rights\GlobalRightsDTO;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\VersionControlException;
use App\Facades\Helpers;
use App\Facades\Lang;
use App\Http\Requests\Session\LoginRequest;
use App\Legacy\AdminAuthentication;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use App\Policies\SettingsPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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
	 * @return array
	 *
	 * @throws VersionControlException
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 * @throws ModelDBException
	 * @throws InvalidOrderDirectionException
	 */
	public function init(): array
	{
		try {
			// Return settings
			$return = [];

			if (AdminAuthentication::loginAsAdminIfNotRegistered()) {
				// TODO: Remove this legacy stuff after creating the admin user has become part of the installation routine.
				// If the session is unauthenticated ('user' === null), but grants admin rights nonetheless,
				// the front-end shows the dialog to create an admin account.
				$return['user'] = null;
				$return['rights'] = GlobalRightsDTO::ofUnregisteredAdmin();
			} else {
				/** @var User|null $user */
				$user = Auth::user();
				$return['user'] = $user?->toArray();
				$return['rights'] = GlobalRightsDTO::ofCurrentUser();
			}

			// Load configuration settings acc. to authentication status
			if (Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class])) {
				// Admin rights (either properly authenticated or not registered)
				$return['config'] = $this->configFunctions->admin();
				$return['config']['location'] = base_path('public/');
				$return['config']['lang_available'] = Lang::get_lang_available();
			} elseif ($return['user'] !== null) {
				// Authenticated as non-admin
				$return['config'] = $this->configFunctions->public();
				$return['config']['lang_available'] = Lang::get_lang_available();
			} else {
				// Unauthenticated
				$return['config'] = $this->configFunctions->public();
				if (Configs::getValueAsBool('hide_version_number')) {
					$return['config']['version'] = '';
				}
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
		} catch (ModelDBException $e) {
			$this->logout();
			throw $e;
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
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
		if (AdminAuthentication::loginAsAdmin($request->username(), $request->password(), $request->ip())) {
			return;
		}

		if (Auth::attempt(['username' => $request->username(), 'password' => $request->password()])) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $request->username() . ') has logged in from ' . $request->ip());

			return;
		}

		// TODO: We could avoid this separate log entry and let the exception handler to all the logging, if we would add "context" (see Laravel docs) to those exceptions which need it.
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
