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
	public string $fullname;
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
		return [
			'user' => $this->user,
			'server' => $this->server,
			'dn' => $this->dn,
			'fullname' => $this->fullname,
			'email' => $this->email,
		];
	}
}
