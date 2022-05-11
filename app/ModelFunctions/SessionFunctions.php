<?php

namespace App\ModelFunctions;

use App\Actions\User\Create;
use App\Exceptions\LDAPException;
use App\Exceptions\UnauthenticatedException;
use App\Legacy\Legacy;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SessionFunctions
{
	/* @var resource $con holds the LDAP connection */
	protected $con = null;

	/* @var int $bound What type of connection does already exist? */
	protected $bound = 0; // 0: anonymous, 1: user, 2: superuser

	/* @var array user info queried from LDAP */
	protected $user_info = [];

	public ?User $user_data = null;

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
	 */
	public function log_as_user(string $username, string $password, string $ip): bool
	{
		if (Configs::get_value('ldap_enabled', '0')) {
			$valid = $this->_checkPass($username, $password);
			if ($valid) {
				$info = $this->_getUserData($username);
				$user = User::query()->where('username', '=', $username)->where('id', '>', '0')->first();
				if ($user == null) {
					$create = new Create();
					$create->do($username, $password, false, true, $info['email'], $info['fullname']);
					$user = User::query()->where('username', '=', $username)->where('id', '>', '0')->first();
				}
				if ($user != null) {
					$this->user_data = $user;
					Session::put('login', true);
					Session::put('UserID', $user->id);
					if (($user->fullname != $info['fullname']) || ($user->email != $info['email'])) {
						$user->email = $info['email'];
						$user->fullname = $info['fullname'];
						$user->save();
					}
					Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

					return true;
				}

				return false;
			} else {
				return false;
			}
		} else {
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
		$this->user_info = [];
		Session::flush();
	}

	/**
	 * Log debug message.
	 *
	 * @param string $method
	 * @param string $line
	 * @param string $text
	 */
	protected function _debug(string $method, string $line, string $text)
	{
		if ($this->getConf('debug')) {
			Logs::notice($method, $line, $text);
		}
	}

	/**
	 * Wraps around ldap_search, ldap_list or ldap_read depending on $scope.
	 *
	 * @param resource   $link_identifier
	 * @param string     $base_dn
	 * @param string     $filter
	 * @param string     $scope           can be 'base', 'one' or 'sub'
	 * @param array|null $attributes
	 * @param int        $attrsonly
	 * @param int        $sizelimit
	 *
	 * @return resource
	 */
	protected function _ldapsearch($link_identifier, $base_dn, $filter, $scope = 'sub', $attributes = null,
						 $attrsonly = 0, $sizelimit = 0)
	{
		if (is_null($attributes)) {
			$attributes = [];
		}

		if ($scope == 'base') {
			return @ldap_read(
				$link_identifier, $base_dn, $filter, $attributes,
				$attrsonly, $sizelimit
			);
		} elseif ($scope == 'one') {
			return @ldap_list(
				$link_identifier, $base_dn, $filter, $attributes,
				$attrsonly, $sizelimit
			);
		} else {
			return @ldap_search(
				$link_identifier, $base_dn, $filter, $attributes,
				$attrsonly, $sizelimit
			);
		}
	}

	/**
	 * Reads the user data from the LDAP server.
	 *
	 * @param string $user
	 *
	 * @return array containing user data or false
	 */
	protected function _getUserData($user)
	{
		global $conf;
		if (!$this->_openLDAP()) {
			return false;
		}
		if (!empty($this->user_info) && in_array($user, $this->user_info)) {
			$this->_debug(__METHOD__, __LINE__, "getUserData: Use cached info for $user");

			return $this->user_info[$user];
		}
		// force superuser bind if wanted and not bound as superuser yet
		if ($this->getConf('binddn') && $this->getConf('bindpw') && $this->bound < 2) {
			// use superuser credentials
			if (!@ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'))) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP bind as superuser failed.');

				return false;
			}
			$this->bound = 2;
		}

		$info = [];
		$info['user'] = $user;
		$info['server'] = $this->getConf('server');

		// get info for given user
		$base = $this->_makeFilter($this->getConf('usertree'), $info);
		$this->_debug(__METHOD__, __LINE__, 'base filter: ' . $base);
		if ($this->getConf('userfilter')) {
			$filter = $this->_makeFilter($this->getConf('userfilter'), $info);
		} else {
			$filter = '(ObjectClass=*)';
		}
		$this->_debug(__METHOD__, __LINE__, 'filter: ' . $filter);

		$sr = $this->_ldapsearch($this->con, $base, $filter, $this->getConf('userscope'));
		$result = @ldap_get_entries($this->con, $sr);
		ldap_free_result($sr);

		// if result is not an array
		if (!is_array($result)) {
			// no objects found
			Logs::notice(__METHOD__, __LINE__, 'LDAP search returned non-array result.');

			return false;
		}

		// Only accept one response
		if ($result['count'] != 1) {
			Logs::notice(__METHOD__, __LINE__, 'LDAP search returned ' . $result['count'] . ' results while it should return 1!');

			return false;
		}

		$user_result = $result[0];

		// general user info
		$info['dn'] = $user_result['dn'];
		$info['fullname'] = $user_result[$this->getConf('cn', 'cn')][0];
		if (array_key_exists($this->getConf('mail', 'mail'), $user_result)) {
			$info['email'] = $user_result[$this->getConf('mail', 'mail')][0];
		} else {
			$info['email'] = '';
		}
		// cache the info for future use
		$this->user_info[$user] = $info;

		return $info;
	}

	/**
	 * Check user+password.
	 *
	 * Checks if the given user exists and the given
	 * plaintext password is correct by trying to bind
	 * to the LDAP server
	 *
	 * @param string $user
	 * @param string $pass
	 *
	 * @return bool
	 */
	protected function _checkPass($user, $pass)
	{
		if (!$this->_openLDAP()) {
			return false;
		}

		// indirect user bind
		if ($this->getConf('binddn') && $this->getConf('bindpw')) {
			// use superuser credentials
			if (!@ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'))) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP bind as superuser failed.');

				return false;
			}
			$this->bound = 2;
		} elseif ($this->getConf('binddn') &&
			$this->getConf('usertree') &&
			$this->getConf('userfilter')
		) {
			// special bind string
			$dn = $this->_makeFilter(
				$this->getConf('binddn'),
				['user' => $user, 'server' => $this->getConf('server')]
			);
		} elseif (strpos($this->getConf('usertree'), '%{user}')) {
			// direct user bind
			$dn = $this->_makeFilter(
				$this->getConf('usertree'),
				['user' => $user, 'server' => $this->getConf('server')]
			);
		} else {
			// Anonymous bind
			if (!@ldap_bind($this->con)) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP: can not bind anonymously');

				return false;
			}
		}

		// Try to bind to with the dn if we have one.
		if (!empty($dn)) {
			// User/Password bind
			if (!@ldap_bind($this->con, $dn, $pass)) {
				Logs::notice(__METHOD__, __LINE__, "LDAP: bind with $dn failed");

				return false;
			}
			$this->bound = 1;

			return true;
		} else {
			// See if we can find the user
			$info = $this->_getUserData($user);
			if (empty($info['dn'])) {
				return false;
			} else {
				$dn = $info['dn'];
			}

			// Try to bind with the dn provided
			if (!@ldap_bind($this->con, $dn, $pass)) {
				Logs::notice(__METHOD__, __LINE__, "LDAP: bind with $dn failed");

				return false;
			}
			$this->bound = 1;

			return true;
		}
	}

	protected function getConf(string $key, int|bool|string|null $default = null): int|bool|string|null
	{
		$key = 'ldap_' . $key;

		return Configs::get_value($key, $default);
	}

	/**
	 * Escape a string to be used in a LDAP filter.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function _filterEscape($string)
	{
		// see https://github.com/adldap/adLDAP/issues/22
		return preg_replace_callback(
			'/([\x00-\x1F\*\(\)\\\\])/',
			function ($matches) {
				return '\\' . join('', unpack('H2', $matches[1]));
			},
			$string
		);
	}

	/**
	 * Make LDAP filter strings.
	 *
	 * @param string $filter       ldap search filter with placeholders
	 * @param array  $placeholders placeholders to fill in
	 *
	 * @return string
	 */
	protected function _makeFilter($filter, $placeholders)
	{
		preg_match_all('/%{([^}]+)/', $filter, $matches, PREG_PATTERN_ORDER);
		// replace each match
		foreach ($matches[1] as $match) {
			// take first element if array
			if (is_array($placeholders[$match])) {
				$value = $placeholders[$match][0];
			} else {
				$value = $placeholders[$match];
			}
			$value = $this->_filterEscape($value);
			$filter = str_replace('%{' . $match . '}', $value, $filter);
		}

		return $filter;
	}

	/**
	 * Opens a connection to the configured LDAP server and sets the wanted
	 * option on the connection.
	 */
	protected function _openLDAP()
	{
		if ($this->con) {
			return true;
		} // connection already established

		// ldap extension is needed
		if (!function_exists('ldap_connect')) {
			throw new LDAPException('LDAP err: PHP LDAP extension not found.');

			return false;
		}

		$this->bound = 0;

		$port = $this->getConf('port');
		$bound = false;
		$servers = explode(',', $this->getConf('server'));
		foreach ($servers as $server) {
			$server = trim($server);
			$this->con = @ldap_connect($server, $port);
			$OK = $this->con == true;
			$this->_debug(__METHOD__, __LINE__, 'Try to connect ' . $server . ' on port ' . $port . ' = ' . $OK);
			if (!$this->con) {
				continue;
			}

			/*
			 * When OpenLDAP 2.x.x is used, ldap_connect() will always return a resource as it does
			 * not actually connect but just initializes the connecting parameters. The actual
			 * connect happens with the next calls to ldap_* funcs, usually with ldap_bind().
			 *
			 * So we should try to bind to server in order to check its availability.
			 */
			// set protocol version and dependend options
			if ($this->getConf('version')) {
				if (!@ldap_set_option(
					$this->con, LDAP_OPT_PROTOCOL_VERSION,
					$this->getConf('version')
				)
				) {
					Logs::notice(__METHOD__, __LINE__, 'Setting LDAP Protocol version ' . $this->getConf('version') . ' failed' . ' [' . ldap_error($this->con) . ']');
				} else {
					// use TLS (needs version 3)
					if ($this->getConf('starttls')) {
						if (!@ldap_start_tls($this->con)) {
							Logs::notice(__METHOD__, __LINE__, 'Starting TLS failed' . ' [' . ldap_error($this->con) . ']');
						}
					}
					// needs version 3
					if ($this->getConf('referrals') > -1) {
						if (!@ldap_set_option(
							$this->con, LDAP_OPT_REFERRALS,
							$this->getConf('referrals')
						)
						) {
							Logs::notice(__METHOD__, __LINE__, 'Setting LDAP referrals failed' . ' [' . ldap_error($this->con) . ']');
						}
					}
				}
			}

			// set deref mode
			if ($this->getConf('deref')) {
				if (!@ldap_set_option($this->con, LDAP_OPT_DEREF, $this->getConf('deref'))) {
					msg('Setting LDAP Deref mode ' . $this->getConf('deref') . ' failed', -1);
					$this->_debug('LDAP deref set: ' . htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
				}
			}
			/* As of PHP 5.3.0 we can set timeout to speedup skipping of invalid servers */
			if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
				ldap_set_option($this->con, LDAP_OPT_NETWORK_TIMEOUT, 1);
			}

			if ($this->getConf('binddn') && $this->getConf('bindpw')) {
				$bound = @ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'));
				$OK = $bound == true;
				Logs::notice(__METHOD__, __LINE__, 'Bind ' . $this->getConf('binddn') . ' using ' . $this->getConf('bindpw') . ' = ' . $OK);
				$this->bound = 2;
			} else {
				$bound = @ldap_bind($this->con);
				$OK = $bound == true;
				$this->_debug(__METHOD__, __LINE__, 'Bind = ' . $OK);
			}
			if ($bound) {
				break;
			} else {
				$this->_debug(__METHOD__, __LINE__, 'LDAP Error: [' . ldap_error($this->con) . ']');
			}
		}

		if (!$bound) {
			Logs::notice(__METHOD__, __LINE__, "LDAP: couldn't connect to LDAP server");

			return false;
		}

		return true;
	}
}
