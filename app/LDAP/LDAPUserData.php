<?php

namespace App\LDAP;

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

	public const KEYS = ['user', 'server', 'dn', 'display_name', 'email'];

	/**
	 * Basic constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Convert object to Array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$ret = [];
		foreach (self::KEYS as $prop) {
			if (isset($this->{$prop})) {
				$ret[$prop] = $this->{$prop};
			}
		}

		return $ret;
	}

	/**
	 * Convert object to Array.
	 *
	 * @return array
	 */
	public function fromArray(array $userdata): void
	{
		foreach (self::KEYS as $prop) {
			if (array_key_exists($prop, $userdata)) {
				$this->{$prop} = $userdata[$prop];
			}
		}
	}
}
