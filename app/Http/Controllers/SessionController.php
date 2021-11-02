<?php

namespace App\Http\Controllers;

use App\Facades\AccessControl;
use App\Facades\Helpers;
use App\Facades\Lang;
use App\Http\Requests\UserRequests\UsernamePasswordRequest;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Config;
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
	 * @return IlluminateResponse|array (array containing config information or killing the session)
	 */
	public function init()
	{
		$logged_in = AccessControl::is_logged_in();

		// Return settings
		$return = [];

		$return['api_V2'] = true;               // we are using api_V2
		$return['sub_albums'] = true;           // Lychee-laravel does have sub albums

		// Check if login credentials exist and login if they don't
		if (AccessControl::noLogin() === true || $logged_in === true) {
			// we set the user ID (it is set to 0 if there is no login/password = admin)
			$user_id = AccessControl::id();

			if ($user_id == 0) {
				$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');
				$return['admin'] = true;
				$return['upload'] = true; // not necessary

				$return['config'] = $this->configFunctions->admin();

				$return['config']['location'] = base_path('public/');
			} else {
				$user = User::query()->find($user_id);

				if ($user == null) {
					Logs::notice(__METHOD__, __LINE__, 'UserID ' . $user_id . ' not found!');

					return $this->logout();
				} else {
					$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');

					$return['config'] = $this->configFunctions->public();
					$return['lock'] = ($user->lock == '1');         // can user change their password
					$return['upload'] = ($user->upload == '1');     // can user upload ?
					$return['username'] = $user->username;
				}
			}

			// here we say whether we logged in because there is no login/password or if we actually entered a login/password
			$return['config']['login'] = $logged_in;
			$return['config']['lang_available'] = Lang::get_lang_available();
		} else {
			// Logged out
			$return['config'] = $this->configFunctions->public();
			if (Configs::get_value('hide_version_number', '1') != '0') {
				$return['config']['version'] = '';
			}
			$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDOUT');
		}

		$deviceType = Helpers::getDeviceType();
		// UI behaviour needs to be slightly modified if client is a TV
		$return['config_device'] = $this->configFunctions->get_config_device($deviceType);

		// we also return the local
		$return['locale'] = Lang::get_lang(Configs::get_value('lang'));

		$return['update_json'] = 0;
		$return['update_available'] = false;

		$this->gitHubFunctions->checkUpdates($return);

		return $return;
	}

	/**
	 * Login tentative.
	 *
	 * @param UsernamePasswordRequest $request
	 *
	 * @return IlluminateResponse
	 */
	public function login(UsernamePasswordRequest $request): IlluminateResponse
	{
		// No login
		if (AccessControl::noLogin() === true) {
			Logs::warning(__METHOD__, __LINE__, 'DEFAULT LOGIN!');

			return response()->noContent();
		}

		// this is probably sensitive to timing attacks...
		if (AccessControl::log_as_admin($request['username'], $request['password'], $request->ip()) === true) {
			return response()->noContent();
		}

		if (AccessControl::log_as_user($request['username'], $request['password'], $request->ip()) === true) {
			return response()->noContent();
		}

		Logs::error(__METHOD__, __LINE__, 'User (' . $request['username'] . ') has tried to log in from ' . $request->ip());

		return response('', 401);
	}

	/**
	 * Unset the session values.
	 *
	 * @return IlluminateResponse
	 */
	public function logout(): IlluminateResponse
	{
		Session::flush();

		return response()->noContent();
	}

	/**
	 * Show the session values.
	 */
	public function show()
	{
		dd(Session::all());
	}
}
