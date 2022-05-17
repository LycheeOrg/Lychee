<?php

namespace Tests\Feature\Lib;

use App\Exceptions\LDAPException;
use App\LDAP\LDAPFunctions;

/**
 * Taking advantage of inheritance to apply test without adding tests functions to the class.
 */
class LDAPFunctionsTest extends LDAPFunctions
{
	public function testLDAPSearch(
		string $base_dn,
		string $filter,
		string $scope = self::SCOPE_SUB,
		array $attributes = [],
		int $attrsonly = 0,
		int $sizelimit = 0
	): array {
		return $this->LDAP_search($base_dn, $filter, $scope, $attributes, $attrsonly, $sizelimit);
	}

	public function testLDAPBind(?string $bindDN = null, ?string $bindPassword = null): bool
	{
		try {
			$this->LDAP_bind($bindDN, $bindPassword);
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function testOpenLDAP(): bool
	{
		try {
			$this->open_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}
}
