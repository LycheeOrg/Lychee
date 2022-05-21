<?php

namespace Tests\Feature\Lib;

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
				array $attributes = null,
				int $attrsonly = 0,
				int $sizelimit = 0
		): array {
		return parent::LDAP_search($base_dn, $filter, $scope, $attributes, $attrsonly, $sizelimit);
	}

	public function LDAP_open(): void
	{
		parent::open_LDAP();
	}

	public function LDAP_close(): void
	{
		parent::close_LDAP();
	}

	public function LDAP_bind(?string $bindDN = null, ?string $bindPassword = null): bool
	{
		return parent::LDAP_bind($bindDN, $bindPassword);
	}

	public function LDAP_get_bound(): int
	{
		return $this->bound;
	}

	public function LDAP_set_option(int $opt, string $value): void // NOSONAR
	{
		// This function is required for the LDAPTest.
		// Do not remove it!
		parent::LDAP_set_option($opt, $value);
	}

	public function LDAP_get_option(int $opt, array|string|int &$value = null): void // NOSONAR
	{
		parent::LDAP_get_option($opt, $value);
	}

	public static function LDAP_filterEscape(string $string): string // NOSONAR
	{
		return parent::filter_escape($string);
	}

	public static function LDAP_makeFilter(string $filter, array $placeholders): string
	{
		return parent::make_filter($filter, $placeholders);
	}

	public function clear_cache(): void
	{
		$this->cached_user_info = [];
	}

	public function LDAP_start_tls(): void // NOSONAR
	{
		// This function is required for the LDAPTest.
		// Do not remove it!
		parent::LDAP_start_tls();
	}

	public function connect(string $host, int $port = 389, $timeout = 1, $retry = 0) // NOSONAR
	{
		// This function is required for the LDAPTest.
		// Do not remove it!
		return parent::connect($host, $port, $timeout, $retry);
	}
}
