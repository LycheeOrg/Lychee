<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Locale\Lang;
use App\Logs;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{

	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;



	/**
	 * @param ConfigFunctions $configFunctions
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions, SessionFunctions $sessionFunctions)
	{
		$this->configFunctions = $configFunctions;
		$this->sessionFunctions = $sessionFunctions;
	}



	/**
	 * First function being called via AJAX
	 *
	 * @param Request $request  (is not used)
	 * @return array|bool       (array containing config information or killing the session)
	 */
	public function init(Request $request)
	{

		$logged_in = $this->sessionFunctions->is_logged_in();

		// Return settings
		$return = array();

		$return['api_V2'] = true;               // we are using api_V2
		$return['sub_albums'] = true;           // Lychee-laravel does have sub albums


		// Check if login credentials exist and login if they don't
		if ($this->sessionFunctions->noLogin() === true || $logged_in === true) {

			// we the the UserID (it is set to 0 if there is no login/password = admin)
			$user_id = Session::get('UserID');

			if ($user_id == 0) {

				$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');
				$return['admin'] = true;
				$return['upload'] = true; // not necessary

				$return['config'] = $this->configFunctions->admin();
				$return['config']['location'] = Config::get('defines.path.LYCHEE');
			}
			else {

				$user = User::find($user_id);

				if ($user == null) {
					Logs::notice(__METHOD__, __LINE__, 'UserID '.$user_id.' not found!');
					return $this->logout();

				}
				else {
					$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');

					$return['config'] = $this->configFunctions->min_info();
					$return['lock'] = ($user->lock == '1');         // can user change his password
					$return['upload'] = ($user->upload == '1');     // can user upload ?
				}
			}

			// here we say whether we looged in because there is no login/password or if we actually entered a login/password
			$return['config']['login'] = $logged_in;

		}
		else {
			// Logged out
			$return['config'] = $this->configFunctions->public();
			$return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDOUT');
		}

		// we also return the local
		$return['locale'] = Lang::get_lang(Configs::get_value('lang'));

		$return['update_json'] = 0;
		$return['update_available'] = false;

		$this->sessionFunctions->checkUpdates($return);

		return $return;

	}



	/**
	 * Login tentative
	 *
	 * @param Request $request
	 * @return string
	 */
	public function login(Request $request)
	{
		$request->validate([
			'user'     => 'required',
			'password' => 'required'
		]);

		// No login
		if ($this->sessionFunctions->noLogin() === true) {
			Logs::warning(__METHOD__, __LINE__, 'DEFAULT LOGIN!');
			return 'true';
		}

		$configs = Configs::get();

		// this is probably sensitive to timing attacks...
		$user = User::where('username', '=', $request['user'])->first();

		if (Hash::check($request['user'], $configs['username']) && Hash::check($request['password'], $configs['password'])) {
			Session::put('login', true);
			Session::put('UserID', 0);
			Logs::notice(__METHOD__, __LINE__, 'User ('.$request['user'].') has logged in from '.$request->ip());
			return 'true';
		}

		if ($user != null && Hash::check($request['password'], $user->password)) {
			Session::put('login', true);
			Session::put('UserID', $user->id);
			Logs::notice(__METHOD__, __LINE__, 'User ('.$request['user'].') has logged in from '.$request->ip());
			return 'true';
		}

		Logs::error(__METHOD__, __LINE__, 'User ('.$request['user'].') has tried to log in from '.$request->ip());

		return 'false';

	}



	/**
	 * Unsets the session values.
	 * @return boolean Returns true when logout was successful.
	 */
	public function logout()
	{

		Session::flush();

		return 'true';

	}



	/**
	 * Show the session values
	 */
	public function show()
	{
		dd(Session::all());
	}



	/**
	 * @param $request
	 * @param string $albumID
	 * @return int
	 */
	static public function checkAccess($request, $albumID = '')
	{
		if (Session::get('login')) {
			return 1;
		}

		if ($albumID != '') {
			$album = Album::find($albumID);
		}
		else {
			$album = Album::find($request['albumID']);
		}
		if ($album == null) {
			return 0;
		} // Does not exist
		if ($album->public != 1) {
			return 2;
		} // Warning: Album private!
		if ($album->password == '') {
			return 1;
		}

		if (!Session::has('visible_albums')) {
			return 3;
		} // Please enter password first. // Warning: Wrong password!

		$visible_albums = Session::get('visible_albums');
		$visible_albums = explode('|', $visible_albums);
		$found = false;
		foreach ($visible_albums as $visible_album) {
			$found |= ($visible_album == $request['albumID']);
		}
		if ($found) {
			return 1;
		}

		return 3;  // Please enter password first. // Warning: Wrong password!
	}
}
