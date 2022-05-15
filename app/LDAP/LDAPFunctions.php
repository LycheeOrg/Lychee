<?php

namespace App\LDAP;

use App\Exceptions\LDAPException;
use App\Models\Configs;
use App\Models\Logs;
use LDAP\Result;

class LDAPFunctions
{
	/**
	 * @var \LDAP\Connection holds the LDAP connection */
	protected $con = null;

	/* @var int $bound What type of connection does already exist? */
	protected int $bound = 0; // 0: anonymous, 1: user, 2: superuser

	/* @var array user info queried from LDAP */
	protected $user_info = [];

	/**
	 * Wraps around ldap_search, ldap_list or ldap_read depending on $scope.
	 *
	 * @param string $base_dn
	 * @param string $filter
	 * @param string $scope      can be 'base', 'one' or 'sub'
	 * @param array  $attributes
	 * @param int    $attrsonly
	 * @param int    $sizelimit
	 *
	 * @return Result|array|false
	 */
	public function LDAP_search(
		$base_dn,
		$filter,
		$scope = 'sub',
		$attributes = [],
		$attrsonly = 0,
		$sizelimit = 0
	): Result|array|false {
		$link_identifier = $this->con;
		try {
			if ($scope == 'base') {
				$sr = ldap_read(
					$link_identifier,
					$base_dn,
					$filter,
					$attributes,
					$attrsonly,
					$sizelimit
				);
			} elseif ($scope == 'one') {
				$sr = ldap_list(
					$link_identifier,
					$base_dn,
					$filter,
					$attributes,
					$attrsonly,
					$sizelimit
				);
			} else {
				$sr = ldap_search(
					$link_identifier,
					$base_dn,
					$filter,
					$attributes,
					$attrsonly,
					$sizelimit
				);
			}
			if (!$sr) {
				return null;
			}
			$result = ldap_get_entries($this->con, $sr);
			ldap_free_result($sr);

			return $result;
		} catch (\Throwable $e) {
			Logs::notice(__METHOD__, __LINE__, sprintf('LDAP_dearch failed [%s]', ldap_error($this->con)));
		}

		return false;
	}

	/**
	 * Wraps around ldap_bind.
	 *
	 * @return bool
	 */
	public function LDAP_bind($bdn = null, $bpw = null): bool
	{
		$bdn = $bdn ?? Configs::get_value('ldap_binddn');
		$bpw = $bpw ?? Configs::get_value('ldap_bindpw');

		try {
			return ldap_bind($this->con, $bdn, $bpw);
		} catch (\Throwable) {
			Logs::notice(__METHOD__, __LINE__, sprintf('LDAP_bind failed [%s]', ldap_error($this->con)));
		}

		return false;
	}

	/**
	 * Wraps around ldap_set_option.
	 *
	 * @param mixed  $opt
	 * @param string $value
	 *
	 * @return bool
	 */
	protected function LDAP_set_option($opt, string $value): bool
	{
		try {
			return ldap_set_option($this->con, $opt, $value);
		} catch (\Throwable) {
		}

		return false;
	}

	/**
	 * Warp around ldap_start_tls.
	 *
	 * @return bool
	 */
	protected function LDAP_start_tls()
	{
		try {
			return ldap_start_tls($this->con);
		} catch (\Throwable) {
		}

		return false;
	}

	/**
	 * Reads the user data from the LDAP server.
	 *
	 * @param string $user
	 *
	 * @return LDAPUserData|null containing user data
	 */
	public function get_user_data($user): ?LDAPUserData
	{
		if (!$this->open_LDAP()) {
			return null;
		}

		if (!empty($this->user_info) && in_array($user, $this->user_info)) {
			Logs::notice(__METHOD__, __LINE__, sprintf('getUserData: Use cached info for %s', $user));

			return $this->user_info[$user];
		}

		// force superuser bind if wanted and not bound as superuser yet
		if (Configs::get_value('ldap_binddn') && Configs::get_value('ldap_bindpw') && $this->bound < 2) {
			// use superuser credentials
			if (!$this->LDAP_bind()) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP bind as superuser failed.');

				return null;
			}
			$this->bound = 2;
		}

		$userData = new LDAPUserData();
		$userData->user = $user;

		// get info for given user
		$base = $this->_makeFilter(Configs::get_value('ldap_usertree'), $userData->toArray());
		Logs::notice(__METHOD__, __LINE__, sprintf('base filter: %s', $base));
		if (Configs::get_value('ldap_userfilter')) {
			$filter = $this->_makeFilter(Configs::get_value('ldap_userfilter'), $userData->toArray());
		} else {
			$filter = '(ObjectClass=*)';
		}
		Logs::notice(__METHOD__, __LINE__, sprintf('filter: %s', $filter));

		$result = $this->LDAP_search($base, $filter, Configs::get_value('ldap_userscope'));

		// if result is not an array
		if (!is_array($result)) {
			// no objects found
			Logs::notice(__METHOD__, __LINE__, 'LDAP search returned non-array result.');

			return null;
		}

		// Only accept one response
		if ($result['count'] != 1) {
			Logs::notice(__METHOD__, __LINE__, sprintf('LDAP search returned %d results while it should return 1!', $result['count']));

			return null;
		}

		$user_result = $result[0];

		// general user info
		$userData->dn = $user_result['dn'];
		$userData->fullname = $user_result[Configs::get_value('ldap_cn', 'cn')][0];
		if (array_key_exists(Configs::get_value('ldap_mail', 'mail'), $user_result)) {
			$userData->email = $user_result[Configs::get_value('ldap_mail', 'mail')][0];
		} else {
			$userData->email = '';
		}
		// cache the info for future use
		$this->user_info[$user] = $userData;

		return $userData;
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
	public function check_pass($user, $pass): bool
	{
		if (!$this->open_LDAP()) {
			return false;
		}

		$ldap_bindnd = Configs::get_value('ldap_binddn');
		$ldap_server = Configs::get_value('ldap_server');
		// indirect user bind
		if ($ldap_bindnd && Configs::get_value('ldap_bindpw')) {
			// use superuser credentials
			if (!$this->LDAP_bind()) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP bind as superuser failed.');

				return false;
			}
			$this->bound = 2;
		} elseif ($ldap_bindnd && Configs::get_value('ldap_usertree') && Configs::get_value('ldap_userfilter')) {
			// special bind string
			$dn = $this->_makeFilter($ldap_bindnd, ['user' => $user, 'server' => $ldap_server]);
		} elseif (strpos(Configs::get_value('ldap_usertree'), '%{user}')) {
			// direct user bind
			$dn = $this->_makeFilter(
				Configs::get_value('ldap_usertree'),
				['user' => $user, 'server' => $ldap_server]
			);
		} else {
			// Anonymous bind
			if (!$this->LDAP_bind()) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP: can not bind anonymously');

				return false;
			}
		}

		// Try to bind to with the dn if we have one.
		if (!empty($dn)) {
			// User/Password bind
			if (!$this->LDAP_bind($dn, $pass)) {
				Logs::notice(__METHOD__, __LINE__, sprintf('LDAP: bind with %s failed', $dn));

				return false;
			}
			$this->bound = 1;

			return true;
		}

		// See if we can find the user
		$info = $this->get_user_data($user);
		if ($info == null || empty($info->dn)) {
			return false;
		}

		$dn = $info->dn;

		// Try to bind with the dn provided
		if (!$this->LDAP_bind($dn, $pass)) {
			Logs::notice(__METHOD__, __LINE__, sprintf('LDAP: bind with %s failed', $dn));

			return false;
		}
		$this->bound = 1;

		return true;
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
	 *
	 * @throws LDAPException
	 */
	public function open_LDAP()
	{
		if ($this->con) {
			return true;
		} // connection already established

		// ldap extension is needed
		if (!function_exists('ldap_connect')) {
			throw new LDAPException('LDAP err: PHP LDAP extension not found.');
		}

		$this->bound = 0;
		$port = Configs::get_value('ldap_port');
		$servers = explode(',', Configs::get_value('ldap_server'));
		foreach ($servers as $server) {
			$server = trim($server);
			$this->con = ldap_connect($server, $port);
			$OK = ($this->con !== false);
			Logs::notice(__METHOD__, __LINE__, sprintf('Try to connect %s on port %s: %s', $server, $port, $OK ? 'OK' : 'NO'));
			if ($OK) {
				break;
			}
		}

		if (!$OK) {
			Logs::error(__METHOD__, __LINE__, "LDAP: couldn't connect to LDAP server");

			return false;
		}
		/**
		 * We aquired connection \o/.
		 */

		/*
		 * When open_LDAP 2.x.x is used, ldap_connect() will always return a resource as it does
		 * not actually connect but just initializes the connecting parameters. The actual
		 * connect happens with the next calls to ldap_* funcs, usually with ldap_bind().
		 *
		 * So we should try to bind to server in order to check its availability.
		*/
		// set protocol version and dependend options
		$ldap_version = Configs::get_value('ldap_version');
		if ($ldap_version) {
			if (!$this->LDAP_set_option(LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
				Logs::notice(__METHOD__, __LINE__, sprintf('Setting LDAP Protocol version %s failed [%s]', $ldap_version, ldap_error($this->con)));
			} else {
				Logs::notice(__METHOD__, __LINE__, sprintf('Using protocoll version %s', $ldap_version));
				// use TLS (needs version 3)
				if (Configs::get_value('ldap_starttls') && !$this->LDAP_start_tls()) {
					Logs::notice(__METHOD__, __LINE__, sprintf('Starting TLS failed [%s]', ldap_error($this->con)));
				}
			}

			// needs version 3
			$ldap_referals = Configs::get_value('ldap_referrals');
			if (($ldap_referals > -1) && !$this->LDAP_set_option(LDAP_OPT_REFERRALS, $ldap_referals)) {
				Logs::notice(__METHOD__, __LINE__, sprintf('Setting LDAP referrals failed [%s]', ldap_error($this->con)));
			}
		}

		// set deref mode
		$ldap_deref = Configs::get_value('ldap_deref');
		if ($ldap_deref && !$this->LDAP_set_option(LDAP_OPT_DEREF, $ldap_deref)) {
			Logs::notice(__METHOD__, __LINE__, sprintf('Setting LDAP Deref mode %s failed.', $ldap_deref));
		}
		/* As of PHP 5.3.0 we can set timeout to speedup skipping of invalid servers */
		if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
			$this->LDAP_set_option(LDAP_OPT_NETWORK_TIMEOUT, 1);
		}

		if (Configs::get_value('ldap_binddn') && Configs::get_value('ldap_bindpw')) {
			$OK = $this->LDAP_bind();
			Logs::notice(__METHOD__, __LINE__, sprintf('Bind %s using %s: %s', Configs::get_value('ldap_binddn'), Configs::get_value('ldap_bindpw'), $OK ? 'OK' : 'NO'));
			$this->bound = 2;
		} else {
			$OK = $this->LDAP_bind();
			Logs::notice(__METHOD__, __LINE__, sprintf('Bind: %s ', $OK ? 'OK' : 'NO'));
		}

		if (!$OK) {
			Logs::notice(__METHOD__, __LINE__, sprintf('LDAP Error: [%s]', ldap_error($this->con)));

			return false;
		}

		return true;
	}
}
