<?php

namespace App\LDAP;

use App\Models\Configs;

/**
 * Class LDAPUserData.
 *
 * This class contain the base details of the current LDAP user.
 */
class LDAPUserData
{
	public string $user;
	public string $server;
	public string $dn;
	public string $display_name;
	public string $email;

	/**
	 * Basic constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->server = Configs::get_value('ldap_server');
	}

	/**
	 * Convert object to Array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$ret = [];
		foreach (['user', 'server', 'dn', 'display_name', 'email'] as $prop) {
			if (isset($this->{$prop})) {
				$ret[$prop] = $this->{$prop};
			}
		}

		return $ret;
	}
}
