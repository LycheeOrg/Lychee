<?php

namespace App\LDAP;

use App\Exceptions\Handler;
use App\Exceptions\LDAPException;
use App\Models\Configs;
use App\Models\Logs;

class LDAPFunctions
{
	public const SCOPE_BASE = 'base';
	public const SCOPE_ONE = 'one';
	public const SCOPE_SUB = 'sub';

	protected const CONFIG_KEY_BIND_DN = 'ldap_bind_dn';
	protected const CONFIG_KEY_BIND_PW = 'ldap_bind_pw';
	protected const CONFIG_KEY_CN = 'ldap_cn';
	protected const CONFIG_KEY_DEREF = 'ldap_deref';
	protected const CONFIG_KEY_MAIL = 'ldap_mail';
	protected const CONFIG_KEY_PORT = 'ldap_port';
	protected const CONFIG_KEY_REFERRALS = 'ldap_referrals';
	protected const CONFIG_KEY_SERVER = 'ldap_server';
	protected const CONFIG_KEY_START_TLS = 'ldap_start_tls';
	protected const CONFIG_KEY_USER_TREE = 'ldap_user_tree';
	protected const CONFIG_KEY_USER_FILTER = 'ldap_user_filter';
	protected const CONFIG_KEY_USER_SCOPE = 'ldap_user_scope';
	protected const CONFIG_KEY_VERSION = 'ldap_version';

	protected const BIND_TYPE_UNBOUND = -1;
	protected const BIND_TYPE_ANONYMOUS = 0;
	protected const BIND_TYPE_USER = 1;
	protected const BIND_TYPE_SUPER_USER = 2;

	protected const LDAP_VERSION_UNKNOWN = 0;
	protected const LDAP_VERSION_2 = 2;
	protected const LDAP_VERSION_3 = 3;

	/**
	 * @var \LDAP\Connection|resource|null the LDAP connection
	 */
	protected $con = null;

	/**
	 * Type of LDAP binding.
	 *
	 * Either
	 *
	 *  - {@link LDAPFunctions::BIND_TYPE_UNBOUND},
	 *  - {@link LDAPFunctions::BIND_TYPE_ANONYMOUS},
	 *  - {@link LDAPFunctions::BIND_TYPE_USER},
	 *  - {@link LDAPFunctions::BIND_TYPE_SUPER_USER}.
	 *
	 * @var int
	 */
	protected int $bound = self::BIND_TYPE_UNBOUND;

	/** @var LDAPUserData[] cashed results of user info previously queried from LDAP */
	protected array $cached_user_info = [];

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
		$this->LDAP_check_bind();
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
	 * Bind to the LDAP server.
	 *
	 * If credentials are provided, the given credentials are used.
	 * If no credentials are set, then the method checks whether
	 * system-wide "super-user" credentials are configured and use those
	 * to bind.
	 * In any other case, i.e. no explicit credentials are passed and no
	 * super-user credentials are set, the method tries an anonymous bind.
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
		try {
			if (empty($bindDN)) {
				$bindDN = (string) Configs::get_value(self::CONFIG_KEY_BIND_DN);
				$bindPassword = (string) Configs::get_value(self::CONFIG_KEY_BIND_PW);
				$this->bound = $bindDN ? self::BIND_TYPE_ANONYMOUS : self::BIND_TYPE_SUPER_USER;
			} else {
				$this->bound = self::BIND_TYPE_USER;
			}

			\Safe\ldap_bind($this->con, $bindDN, $bindPassword);
		} catch (\Throwable $e) {
			$this->bound = self::BIND_TYPE_UNBOUND;
			throw new LDAPException($e->getMessage(), $e);
		}
	}

	/**
	 * Check if the bound level is sufficient and bind if neccessary.
	 *
	 * @return void
	 *
	 * @throws LDAPException implicit via LDAP_bind()
	 */
	protected function LDAP_check_bind(): void
	{
		// force superuser or anonymous bind if the bound level is not sufficient yet
		if (($this->bound < (Configs::get_value(self::CONFIG_KEY_BIND_DN) && Configs::get_value(self::CONFIG_KEY_BIND_PW)))
				   ? self::BIND_TYPE_SUPER_USER : self::BIND_TYPE_ANONYMOUS) {
			// use anonymous or superuser credentials
			$this->LDAP_bind();
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
	 * @return void
	 *
	 * @throws LDAPException
	 */
	protected function LDAP_start_tls(): void
	{
		try {
			if (!ldap_start_tls($this->con)) {
				throw new \Safe\Exceptions\LdapException('ldap_stat_tls failed');
			}
		} catch (\Throwable $e) {
			throw new LDAPException($e->getMessage(), $e);
		}
	}

	/**
	 * Reads the user data from the LDAP server.
	 *
	 * @param string $username
	 *
	 * @return LDAPUserData contains the user data
	 *
	 * @throws LDAPException
	 */
	public function get_user_data(string $username): LDAPUserData
	{
		$this->open_LDAP();

		if (!empty($this->cached_user_info) && in_array($username, $this->cached_user_info)) {
			Logs::notice(__METHOD__, __LINE__, sprintf('getUserData: Use cached info for %s', $username));

			return $this->cached_user_info[$username];
		}

		$userData = new LDAPUserData();
		$userData->user = $username;

		// get info for given user
		$base = self::_makeFilter(Configs::get_value(self::CONFIG_KEY_USER_TREE), $userData->toArray());
		Logs::notice(__METHOD__, __LINE__, sprintf('base filter: %s', $base));
		if (Configs::get_value(self::CONFIG_KEY_USER_FILTER)) {
			$filter = self::_makeFilter(Configs::get_value(self::CONFIG_KEY_USER_FILTER), $userData->toArray());
		} else {
			$filter = '(ObjectClass=*)';
		}
		Logs::notice(__METHOD__, __LINE__, sprintf('filter: %s', $filter));

		$result = $this->LDAP_search($base, $filter, Configs::get_value(self::CONFIG_KEY_USER_SCOPE));

		// Only accept one response
		if ($result['count'] != 1) {
			throw new LDAPException(sprintf('LDAP search returned %d results while it should return 1!', $result['count']));
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
		$this->cached_user_info[$username] = $userData;

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
	 *
	 * @throws LDAPException
	 */
	public function check_pass(string $user, string $pass): bool
	{
		$this->open_LDAP();

		$ldap_server = Configs::get_value(self::CONFIG_KEY_SERVER);

		// Option A: If we know how to bind a user, we try that directly

		try {
			if (strpos(Configs::get_value(self::CONFIG_KEY_USER_TREE), '%{user}')) {
				// direct user bind
				$dn = self::_makeFilter(
					Configs::get_value(self::CONFIG_KEY_USER_TREE),
					['user' => $user, 'server' => $ldap_server]
				);
				// User/Password bind
				$this->LDAP_bind($dn, $pass);

				return true;
			}
		} catch (LDAPException) {
			return false;
		}

		// Option B: We do not know how to bind a user, so we must first
		// search the directory

		$this->LDAP_bind(); // anonymous or super-user binding

		// See if we can find the user
		$info = $this->get_user_data($user);
		if (empty($info->dn)) {
			return false;
		}

		$dn = $info->dn;

		// Try to re-bind with the dn provided
		try {
			$this->LDAP_bind($dn, $pass);
		} catch (LDAPException) {
			return false;
		}

		return true;
	}

	/**
	 * Escape a string to be used in an LDAP filter.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected static function _filterEscape(string $string): string
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
	protected static function _makeFilter(string $filter, array $placeholders): string
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
			$value = self::_filterEscape($value);
			$filter = str_replace('%{' . $match . '}', $value, $filter);
		}

		return $filter;
	}

	/**
	 * Prepares/opens a connection to the configured LDAP server and sets the
	 * wanted option on the connection.
	 *
	 * This method does not yet bind to the server.
	 * If no super-user credentials are set, this method cannot decide
	 * whether it should bind anonymously or use user credentials.
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
		$lastException = null;
		foreach ($servers as $server) {
			try {
				$lastException = null;
				$server = trim($server);
				$this->con = ldap_connect($server, $port);
				$OK = ($this->con !== false);
				Logs::notice(__METHOD__, __LINE__, sprintf('Try to connect %s on port %s: %s', $server, $port, $OK ? 'OK' : 'NO'));
				if ($OK) {
					break;
				} else {
					throw new \Safe\Exceptions\LdapException('ldap_connect failed');
				}
			} catch (\Throwable $e) {
				Handler::reportSafely($e);
				$lastException = new LDAPException($e->getMessage(), $e);
			}
		}

		if ($lastException) {
			throw $lastException;
		}

		/**
		 * We have acquired a connection \o/.
		 */

		/*
		 * When open_LDAP 2.x.x is used, ldap_connect() will always return a resource as it does
		 * not actually connect but just initializes the connecting parameters. The actual
		 * connect happens with the next calls to ldap_* functions, usually with ldap_bind().
		 *
		 * So we should try to bind to server in order to check its availability.
		*/

		// set protocol version
		$ldap_version = (int) Configs::get_value(self::CONFIG_KEY_VERSION, self::LDAP_VERSION_UNKNOWN);
		if ($ldap_version !== self::LDAP_VERSION_UNKNOWN) {
			$this->LDAP_set_option(LDAP_OPT_PROTOCOL_VERSION, $ldap_version);
			Logs::notice(__METHOD__, __LINE__, sprintf('Using protocol version %s', $ldap_version));
		}

		// Some options are only valid in combination with version 3
		if ($ldap_version === self::LDAP_VERSION_3) {
			// use TLS (needs version 3)
			if (Configs::get_value(self::CONFIG_KEY_START_TLS)) {
				$this->LDAP_start_tls();
			}

			$ldap_referals = Configs::get_value(self::CONFIG_KEY_REFERRALS);
			if ($ldap_referals > -1) {
				$this->LDAP_set_option(LDAP_OPT_REFERRALS, $ldap_referals);
			}
		}

		// set deref mode
		$ldap_deref = Configs::get_value(self::CONFIG_KEY_DEREF);
		if ($ldap_deref) {
			$this->LDAP_set_option(LDAP_OPT_DEREF, $ldap_deref);
		}
		$this->LDAP_set_option(LDAP_OPT_NETWORK_TIMEOUT, 1);
	}

	/**
	 * The following functions are an interface for the unit test only!!!
	 *
	 * DO NOT USE THEM FOR ANY OTHER PURPOSE!
	 */
	public function test_LDAP_search(
				string $base_dn,
				string $filter,
				string $scope = self::SCOPE_SUB,
				array $attributes = [],
				int $attrsonly = 0,
				int $sizelimit = 0
		): array {
		return $this->LDAP_search($base_dn, $filter, $scope, $attributes, $attrsonly, $sizelimit);
	}

	public function test_LDAP_bind(?string $bindDN = null, ?string $bindPassword = null): bool
	{
		try {
			$this->LDAP_bind($bindDN, $bindPassword);
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function test_open_LDAP(): bool
	{
		try {
			$this->open_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}
	/*
	 * End of test functions
	 */
}
