<?php

namespace Tests;

use App\Models\Configs;
use Tests\Feature\Lib\LDAPTestFunctions;

class LDAPTestCase extends TestCase
{
	// See https://www.forumsys.com/2022/05/10/online-ldap-test-server/
	public const TESTUSER = 'gauss';
	public const TESTUSER_PW = 'password';
	public const TESTUSER2 = 'euler';
	public const TESTUSER2_PW = 'password';
	public const TESTUSER_DN = 'uid=gauss,dc=example,dc=com';
	public const TESTUSER_CN = 'Carl Friedrich Gauss';
	public const TESTUSER_EMAIL = 'gauss@ldap.forumsys.com';
	public const TESTUSER_FILTER = '(uid=gauss)';
	public const SERVER = 'ldap.forumsys.com';
	public const USER_TREE = 'dc=example,dc=com';
	public const USER_FILTER = '(uid=%{user})';
	public const BIND_DN = 'cn=read-only-admin,dc=example,dc=com';
	public const BIND_PW = 'password';
	public const UNKNOWN_USER = 'test4711';

	public $oldconfigs = null;
	private $ldap_test = null;
	protected static $EnableLDAPTests = true;
	protected static $CheckLDAPTestServer = true;

	public static function settings($options): void
	{
		foreach ($options as $key => $value) {
			Configs::set($key, $value);
		}
	}

	public static function _debug($myDebugVar, $label = '', $oneline = true)
	{
		$msg = print_r($myDebugVar, true);
		if ($oneline) {
			$msg = str_replace(PHP_EOL, ' ', $msg);
			while (str_contains($msg, '  ')) {
				$msg = str_replace('  ', ' ', $msg);
			}
		}
		error_log($label . "'" . trim($msg) . "'");
	}

	protected function LDAP_setUp(): void
	{
		$this->oldconfigs = Configs::get();
		self::settings([
			'ldap_server' => self::SERVER,
			'ldap_start_tls' => '0',
			'ldap_user_tree' => self::USER_TREE,
			'ldap_user_filter' => self::USER_FILTER,
			'ldap_bind_dn' => self::BIND_DN,
			'ldap_bind_pw' => self::BIND_PW,
			'ldap_timeout' => '2',
		]);
	}

	protected function LDAP_tearDown(): void
	{
		self::settings($this->oldconfigs);
	}

	protected function get_ldap()
	{
		if (is_null($this->ldap_test)) {
			$this->ldap_test = new LDAPTestFunctions();
		}

		if (self::$CheckLDAPTestServer) {
			self::$CheckLDAPTestServer = false;
			$con = $this->ldap_test->connect('ldap.forumsys.com', 389, 2, 15);
			if (!$con) {
				self::$EnableLDAPTests = false;
			} else {
				ldap_close($con);
			}
		}

		if (!self::$EnableLDAPTests) {
			$this->markTestSkipped('LDAP test-server is not available. Test Skipped.');

			return null;
		}

		$this->LDAP_setUp();

		return $this->ldap_test;
	}

	protected function done_ldap()
	{
		$this->LDAP_tearDown();
	}

	protected function LDAP_open()
	{
		$this->ldap_test->LDAP_open();
	}
}
