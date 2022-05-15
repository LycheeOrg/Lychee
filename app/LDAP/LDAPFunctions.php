<?php

namespace App\LDAP;

use App\Exceptions\LDAPException;
use App\Models\Configs;
use App\Models\Logs;

class LDAPFunctions
{
	public const SCOPE_BASE = 'base';
	public const SCOPE_ONE = 'one';
	public const SCOPE_SUB = 'sub';

	protected const CONFIG_KEY_BIND_DN = 'ldap_binddn';
	protected const CONFIG_KEY_BIND_PW = 'ldap_bindpw';
	protected const CONFIG_KEY_CN = 'ldap_cn';
	protected const CONFIG_KEY_DEREF = 'ldap_deref';
	protected const CONFIG_KEY_MAIL = 'ldap_mail';
	protected const CONFIG_KEY_PORT = 'ldap_port';
	protected const CONFIG_KEY_REFERRALS = 'ldap_referrals';
	protected const CONFIG_KEY_SERVER = 'ldap_server';
	protected const CONFIG_KEY_START_TLS = 'ldap_starttls';
	protected const CONFIG_KEY_USER_TREE = 'ldap_usertree';
	protected const CONFIG_KEY_USER_FILTER = 'ldap_userfilter';
	protected const CONFIG_KEY_USER_SCOPE = 'ldap_userscope';
	protected const CONFIG_KEY_VERSION = 'ldap_version';

	protected const BIND_TYPE_ANONYMOUS = 0;
	protected const BIND_TYPE_USER = 1;
	protected const BIND_TYPE_SUPER_USER = 2;

	/**
	 * @var \LDAP\Connection|resource|null the LDAP connection
	 */
	protected $con = null;

	/**
	 * Type of LDAP binding.
	 *
	 * Either {@link LDAPFunctions::BIND_TYPE_ANONYMOUS},
	 * {@link LDAPFunctions::BIND_TYPE_USER},
	 * {@link LDAPFunctions::BIND_TYPE_SUPER_USER}.
	 *
	 * @var int
	 */
	protected int $bound = self::BIND_TYPE_ANONYMOUS;

	/** @var LDAPUserData[] user info queried from LDAP */
	protected array $user_info = [];

	/**
	 * Wraps around ldap_search, ldap_list or ldap_read depending on $scope.
	 *
	 * @param string $base_dn
	 * @param string $filter
	 * @param string $scope      either {@link LDAPFunctions::SCOPE_BASE}, {@link LDAPFunctions::SCOPE_ONE} or {@link LDAPFunctions::SCOPE_SUB}
	 * @param array  $attributes
	 * @param int    $attrsonly
	 * @param int    $sizelimit
	 *
	 * @return array
	 *
	 * @throws LDAPException
	 */
	protected function LDAP_search(
		string $base_dn,
		string $filter,
		string $scope = self::SCOPE_SUB,
		array $attributes = [],
		int $attrsonly = 0,
		int $sizelimit = 0
	): array {
		try {
			$sr = match ($scope) {
				self::SCOPE_BASE => \Safe\ldap_read(
					$this->con,
					$base_dn,
					$filter,
					$attributes,
					$attrsonly,
					$sizelimit
				),
				self::SCOPE_ONE => \Safe\ldap_list(
					$this->con,
					$base_dn,
					$filter,
					$attributes,
					$attrsonly,
					$sizelimit
				),
				self::SCOPE_SUB => \Safe\ldap_search(
					$this->con,
					$base_dn,
					$filter,
					$attributes,
					$attrsonly,
					$sizelimit
				)
			};
			$result = \Safe\ldap_get_entries($this->con, $sr);
			\Safe\ldap_free_result($sr);

			return $result;
		} catch (\Throwable $e) {
			throw new LDAPException($e->getMessage(), $e);
		}
	}

	/**
	 * Wraps around ldap_bind.
	 *
	 * @param string|null $bindDN
	 * @param string|null $bindPassword
	 *
	 * @return void
	 *
	 * @throws LDAPException
	 */
	protected function LDAP_bind(?string $bindDN = null, ?string $bindPassword = null): void
	{
		$bindDN = $bindDN ?? Configs::get_value(self::CONFIG_KEY_BIND_DN);
		$bindPassword = $bindPassword ?? Configs::get_value(self::CONFIG_KEY_BIND_PW);

		try {
			\Safe\ldap_bind($this->con, $bindDN, $bindPassword);
		} catch (\Throwable $e) {
			throw new LDAPException($e->getMessage(), $e);
		}
	}

	/**
	 * Wraps around ldap_set_option.
	 *
	 * @param int    $opt
	 * @param string $value
	 *
	 * @return void
	 *
	 * @throws LDAPException
	 */
	protected function LDAP_set_option(int $opt, string $value): void
	{
		try {
			\Safe\ldap_set_option($this->con, $opt, $value);
		} catch (\Throwable $e) {
			throw new LDAPException($e->getMessage(), $e);
		}
	}

	/**
	 * Warp around ldap_start_tls.
	 *
	 * @return bool
	 */
	protected function LDAP_start_tls(): bool
	{
		try {
			return ldap_start_tls($this->con);
		} catch (\Throwable) {
			return false;
		}
	}

	/**
	 * Reads the user data from the LDAP server.
	 *
	 * @param string $user
	 *
	 * @return LDAPUserData|null containing user data
	 *
	 * @throws LDAPException
	 */
	public function get_user_data(string $user): ?LDAPUserData
	{
		if (!$this->open_LDAP()) {
			return null;
		}

		if (!empty($this->user_info) && in_array($user, $this->user_info)) {
			Logs::notice(__METHOD__, __LINE__, sprintf('getUserData: Use cached info for %s', $user));

			return $this->user_info[$user];
		}

		// force superuser bind if wanted and not bound as superuser yet
		if (Configs::get_value(self::CONFIG_KEY_BIND_DN) && Configs::get_value(self::CONFIG_KEY_BIND_PW) && $this->bound < 2) {
			// use superuser credentials
			$this->LDAP_bind();
			$this->bound = 2;
		}

		$userData = new LDAPUserData();
		$userData->user = $user;

		// get info for given user
		$base = $this->_makeFilter(Configs::get_value(self::CONFIG_KEY_USER_TREE), $userData->toArray());
		Logs::notice(__METHOD__, __LINE__, sprintf('base filter: %s', $base));
		if (Configs::get_value(self::CONFIG_KEY_USER_FILTER)) {
			$filter = $this->_makeFilter(Configs::get_value(self::CONFIG_KEY_USER_FILTER), $userData->toArray());
		} else {
			$filter = '(ObjectClass=*)';
		}
		Logs::notice(__METHOD__, __LINE__, sprintf('filter: %s', $filter));

		$result = $this->LDAP_search($base, $filter, Configs::get_value(self::CONFIG_KEY_USER_SCOPE));

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
		$userData->display_name = $user_result[Configs::get_value(self::CONFIG_KEY_CN, 'cn')][0];
		if (array_key_exists(Configs::get_value(self::CONFIG_KEY_MAIL, 'mail'), $user_result)) {
			$userData->email = $user_result[Configs::get_value(self::CONFIG_KEY_MAIL, 'mail')][0];
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
	public function check_pass(string $user, string $pass): bool
	{
		if (!$this->open_LDAP()) {
			return false;
		}

		$ldap_bindnd = Configs::get_value(self::CONFIG_KEY_BIND_DN);
		$ldap_server = Configs::get_value(self::CONFIG_KEY_SERVER);
		// indirect user bind
		if ($ldap_bindnd && Configs::get_value(self::CONFIG_KEY_BIND_PW)) {
			// use superuser credentials
			if (!$this->LDAP_bind()) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP bind as superuser failed.');

				return false;
			}
			$this->bound = 2;
		} elseif ($ldap_bindnd && Configs::get_value(self::CONFIG_KEY_USER_TREE) && Configs::get_value(self::CONFIG_KEY_USER_FILTER)) {
			// special bind string
			$dn = $this->_makeFilter($ldap_bindnd, ['user' => $user, 'server' => $ldap_server]);
		} elseif (strpos(Configs::get_value(self::CONFIG_KEY_USER_TREE), '%{user}')) {
			// direct user bind
			$dn = $this->_makeFilter(
				Configs::get_value(self::CONFIG_KEY_USER_TREE),
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
	protected function _filterEscape(string $string): string
	{
		// see https://github.com/adldap/adLDAP/issues/22
		return preg_replace_callback(
			'/([\x00-\x1F*()\\\\])/',
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
	protected function _makeFilter(string $filter, array $placeholders): string
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
	protected function open_LDAP(): void
	{
		if ($this->con) {
			return;
		} // connection already established

		// ldap extension is needed
		if (!extension_loaded('ldap')) {
			throw new LDAPException('PHP LDAP extension not found.');
		}

		$this->bound = 0;
		$port = Configs::get_value(self::CONFIG_KEY_PORT);
		$servers = explode(',', Configs::get_value(self::CONFIG_KEY_SERVER));
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
		$ldap_version = Configs::get_value(self::CONFIG_KEY_VERSION);
		if ($ldap_version) {
			if (!$this->LDAP_set_option(LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
				Logs::notice(__METHOD__, __LINE__, sprintf('Setting LDAP Protocol version %s failed [%s]', $ldap_version, ldap_error($this->con)));
			} else {
				Logs::notice(__METHOD__, __LINE__, sprintf('Using protocol version %s', $ldap_version));
				// use TLS (needs version 3)
				if (Configs::get_value(self::CONFIG_KEY_START_TLS) && !$this->LDAP_start_tls()) {
					Logs::notice(__METHOD__, __LINE__, sprintf('Starting TLS failed [%s]', ldap_error($this->con)));
				}
			}

			// needs version 3
			$ldap_referals = Configs::get_value(self::CONFIG_KEY_REFERRALS);
			if (($ldap_referals > -1) && !$this->LDAP_set_option(LDAP_OPT_REFERRALS, $ldap_referals)) {
				Logs::notice(__METHOD__, __LINE__, sprintf('Setting LDAP referrals failed [%s]', ldap_error($this->con)));
			}
		}

		// set deref mode
		$ldap_deref = Configs::get_value(self::CONFIG_KEY_DEREF);
		if ($ldap_deref && !$this->LDAP_set_option(LDAP_OPT_DEREF, $ldap_deref)) {
			Logs::notice(__METHOD__, __LINE__, sprintf('Setting LDAP Deref mode %s failed.', $ldap_deref));
		}
		$this->LDAP_set_option(LDAP_OPT_NETWORK_TIMEOUT, 1);

		if (Configs::get_value(self::CONFIG_KEY_BIND_DN) && Configs::get_value(self::CONFIG_KEY_BIND_PW)) {
			$OK = $this->LDAP_bind();
			Logs::notice(__METHOD__, __LINE__, sprintf('Bind %s using %s: %s', Configs::get_value(self::CONFIG_KEY_BIND_DN), Configs::get_value(self::CONFIG_KEY_BIND_PW), $OK ? 'OK' : 'NO'));
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
