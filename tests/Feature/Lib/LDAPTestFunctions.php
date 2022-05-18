<?php

namespace Tests\Feature\Lib;

use App\Exceptions\LDAPException;
use App\LDAP\LDAPFunctions;

/**
 * Taking advantage of inheritance to apply test without adding tests functions to the class.
 */
class LDAPTestFunctions extends LDAPFunctions
{
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

	public function test_LDAP_open(): bool
	{
		try {
			$this->open_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function test_LDAP_close(): bool
	{
		try {
			$this->close_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function test_LDAP_bind(?string $bindDN = null, ?string $bindPassword = null): bool
	{
		return $this->LDAP_bind($bindDN, $bindPassword);
	}

	public function test_LDAP_get_bound(): int
	{
		return $this->bound;
	}

	public function test_LDAP_set_option(int $opt, string $value): void
	{
		$this->LDAP_set_option($opt, $value);
	}

	public function test_LDAP_get_option(int $opt, array|string|int &$value = null): void
	{
		$this->LDAP_get_option($opt, $value);
	}

	public static function test_LDAP_filterEscape(string $string): string
	{
		return LDAPFunctions::_filterEscape($string);
	}

	public static function test_LDAP_makeFilter(string $filter, array $placeholders): string
	{
		return LDAPFunctions::_makeFilter($filter, $placeholders);
	}
}
