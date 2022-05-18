<?php

namespace Tests\Feature\Lib;

use App\Exceptions\LDAPException;
use App\LDAP\LDAPFunctions;

/**
 * Taking advantage of inheritance to apply test without adding tests functions to the class.
 */
class LDAPTestFunctions extends LDAPFunctions
{
	public function LDAP_search(
		string $base_dn,
		string $filter,
		string $scope = self::SCOPE_SUB,
		array $attributes = [],
		int $attrsonly = 0,
		int $sizelimit = 0
	): array {
		return parent::LDAP_search($base_dn, $filter, $scope, $attributes, $attrsonly, $sizelimit);
	}

	public function LDAP_open(): bool
	{
		try {
			parent::open_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function LDAP_close(): bool
	{
		try {
			parent::close_LDAP();
		} catch (LDAPException $e) {
			return false;
		}

		return true;
	}

	public function LDAP_bind(?string $bindDN = null, ?string $bindPassword = null): bool
	{
		return parent::LDAP_bind($bindDN, $bindPassword);
	}

	public function LDAP_get_bound(): int
	{
		return $this->bound;
	}

	public function LDAP_set_option(int $opt, string $value): void
	{
		parent::LDAP_set_option($opt, $value);
	}

	public function LDAP_get_option(int $opt, array|string|int &$value = null): void
	{
		parent::LDAP_get_option($opt, $value);
	}

	public static function LDAP_filterEscape(string $string): string
	{
		return parent::_filterEscape($string);
	}

	public static function LDAP_makeFilter(string $filter, array $placeholders): string
	{
		return parent::_makeFilter($filter, $placeholders);
	}
}
