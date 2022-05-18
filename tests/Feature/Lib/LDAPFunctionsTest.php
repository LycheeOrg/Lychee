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
		return $this->LDAP_bind($bindDN, $bindPassword);
	}

	public function testLDAPOpen(): bool
	{
		try {
			$this->open_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function testLDAPClose(): bool
	{
		try {
			$this->close_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function testLDAPGetBound(): int
	{
		return $this->bound;
	}

	public function testLDAPSetOption(int $opt, string $value): void
	{
		$this->LDAP_set_option($opt, $value);
	}

	public function testLDAPGetOption(int $opt, array|string|int &$value = null): void
	{
		$this->LDAP_get_option($opt, $value);
	}

	public static function testLDAPFilterEscape(string $string): string
	{
		return LDAPFunctions::_filterEscape($string);
	}

	public static function testLDAPMakeFilter(string $filter, array $placeholders): string
	{
		return LDAPFunctions::_makeFilter($filter, $placeholders);
	}
}
