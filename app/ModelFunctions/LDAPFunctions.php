<?php

namespace App\ModelFunctions;

use App\Exceptions\LDAPException;
use App\Models\Configs;
use App\Models\Logs;

class LDAPFunctions
{
	/* This class make use of the @-operator and the use is essential for the class not breaking the application
	/* up to php 8.1.5
	/*
	/* At least ldap_bind() fires uncatchable Exceptions and to protect the application we need to suppress them.
	/* Therefore all php ldap library functions are protected using the @-operator. They simply can be deleted once
	/* the php library functions fire only exceptions which can be caught.
	/*
	/* @var resource $con holds the LDAP connection */
	protected $con = null;

	/* @var int $bound What type of connection does already exist? */
	protected int $bound = 0; // 0: anonymous, 1: user, 2: superuser

	/* @var array user info queried from LDAP */
	protected $user_info = [];

	/**
	 * Log debug message.
	 *
	 * @param string $method
	 * @param string $line
	 * @param string $text
	 */
	protected function _debug(string $method, string $line, string $text)
	{
		if ($this->getConf('debug', '0')) {
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
	protected function LDAP_search($link_identifier, $base_dn, $filter, $scope = 'sub', $attributes = null,
						 $attrsonly = 0, $sizelimit = 0)
	{
		try {
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
		} catch (Exception $e) {
			Logs::notice(__METHOD__, __LINE__, 'LDAP_dearch failed' . ' [' . ldap_error($this->con) . ']');

			return false;
		}
	}

	/**
	 * Wraps around ldap_bind.
	 *
	 * @return bool
	 */
	protected function LDAP_bind($bdn = null, $bpw = null): bool
	{
		if (empty($bdn)) {
			$bdn = $this->getConf('binddn');
		}
		if (empty($bpw)) {
			$bpw = $this->getConf('bindpw');
		}
		try {
			// the @-operator is essential here, ldap_bind fires exceptions which cannot be caught using catch otherwise.
			// if ldap_bind is change in future so that it does not fire uncatchable Exceptions, then the @-operator can be delted.
			$ret = @ldap_bind($this->con, $bdn, $bpw);

			return $ret;
		} catch (ErrorException $e) {
			Logs::notice(__METHOD__, __LINE__, 'LDAP_bind failed' . ' [' . ldap_error($this->con) . ']');

			return false;
		}
	}

	protected function LDAP_set_option($opt, string $value): bool
	{
		try {
			return @ldap_set_option($this->con, $opt, $value);
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Reads the user data from the LDAP server.
	 *
	 * @param string $user
	 *
	 * @return array containing user data or false
	 */
	public function get_user_data($user)
	{
		global $conf;
		if (!$this->OpenLDAP()) {
			return false;
		}
		if (!empty($this->user_info) && in_array($user, $this->user_info)) {
			$this->_debug(__METHOD__, __LINE__, "getUserData: Use cached info for $user");

			return $this->user_info[$user];
		}
		// force superuser bind if wanted and not bound as superuser yet
		if ($this->getConf('binddn') && $this->getConf('bindpw') && $this->bound < 2) {
			// use superuser credentials
			if (!$this->LDAP_bind()) {
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

		$sr = $this->LDAP_search($this->con, $base, $filter, $this->getConf('userscope'));
		$result = ldap_get_entries($this->con, $sr);
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
	public function check_pass($user, $pass)
	{
		if (!$this->OpenLDAP()) {
			return false;
		}

		// indirect user bind
		if ($this->getConf('binddn') && $this->getConf('bindpw')) {
			// use superuser credentials
			if (!$this->LDAP_bind()) {
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
			if (!$this->LDAP_bind()) {
				Logs::notice(__METHOD__, __LINE__, 'LDAP: can not bind anonymously');

				return false;
			}
		}

		// Try to bind to with the dn if we have one.
		if (!empty($dn)) {
			// User/Password bind
			if (!$this->LDAP_bind($dn, $pass)) {
				Logs::notice(__METHOD__, __LINE__, "LDAP: bind with $dn failed");

				return false;
			}
			$this->bound = 1;

			return true;
		} else {
			// See if we can find the user
			$info = $this->get_user_data($user);
			if (empty($info['dn'])) {
				return false;
			} else {
				$dn = $info['dn'];
			}

			// Try to bind with the dn provided
			if (!$this->LDAP_bind($dn, $pass)) {
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
		$PAT = '/([\x00-\x1F\*\(\)\\\\])/';

		return preg_replace_callback($PAT,
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
		$PAT = '/%{([^}]+)/';
		preg_match_all($PAT, $filter, $matches, PREG_PATTERN_ORDER);
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
	public function OpenLDAP()
	{
		if ($this->con) {
			return true;
		} // connection already established

		// ldap extension is needed
		if (!function_exists('ldap_connect')) {
			throw new LDAPException('LDAP err: PHP LDAP extension not found.');
		}

		$this->bound = 0;
		$FALSE = false;
		$port = $this->getConf('port');
		$servers = explode(',', $this->getConf('server'));
		foreach ($servers as $server) {
			$server = trim($server);
			$this->con = ldap_connect($server, $port);
			$OK = ($this->con != $FALSE);
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
				if (!$this->LDAP_set_option(LDAP_OPT_PROTOCOL_VERSION, $this->getConf('version'))) {
					Logs::notice(__METHOD__, __LINE__, 'Setting LDAP Protocol version ' . $this->getConf('version') . ' failed' . ' [' . ldap_error($this->con) . ']');
				} else {
					$this->_debug(__METHOD__, __LINE__, 'Using protocoll version ' . $this->getConf('version'));
					// use TLS (needs version 3)
					try {
						if ($this->getConf('starttls') && !ldap_start_tls($this->con)) {
							Logs::notice(__METHOD__, __LINE__, 'Starting TLS failed' . ' [' . ldap_error($this->con) . ']');
						}
					} catch (Exception $e) {
					}
				}
				// needs version 3
				if (($this->getConf('referrals') > -1) && !$this->LDAP_set_option(LDAP_OPT_REFERRALS, $this->getConf('referrals'))) {
					Logs::notice(__METHOD__, __LINE__, 'Setting LDAP referrals failed' . ' [' . ldap_error($this->con) . ']');
				}
			}
			// set deref mode
			if ($this->getConf('deref') && !$this->LDAP_set_option(LDAP_OPT_DEREF, $this->getConf('deref'))) {
				Logs::notice(__METHOD__, __LINE__, 'Setting LDAP Deref mode ' . $this->getConf('deref') . ' failed');
			}
			/* As of PHP 5.3.0 we can set timeout to speedup skipping of invalid servers */
			if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
				$this->LDAP_set_option(LDAP_OPT_NETWORK_TIMEOUT, 1);
			}

			if ($this->getConf('binddn') && $this->getConf('bindpw')) {
				$OK = $this->LDAP_bind();
				$this->_debug(__METHOD__, __LINE__, 'Bind ' . $this->getConf('binddn') . ' using ' . $this->getConf('bindpw') . ' = ' . $OK);
				$this->bound = 2;
			} else {
				$OK = $this->LDAP_bind();
				$this->_debug(__METHOD__, __LINE__, 'Bind = ' . $OK);
			}
			if ($OK) {
				break;
			} else {
				$this->_debug(__METHOD__, __LINE__, 'LDAP Error: [' . ldap_error($this->con) . ']');
			}
		}

		if (!$OK) {
			Logs::notice(__METHOD__, __LINE__, "LDAP: couldn't connect to LDAP server");

			return false;
		}

		return true;
	}
}
