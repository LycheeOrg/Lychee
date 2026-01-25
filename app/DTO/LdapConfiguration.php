<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use Illuminate\Support\Facades\Config;

/**
 * Value object encapsulating LDAP configuration with validation.
 *
 * This DTO loads LDAP settings from config/ldap.php and validates
 * that all required fields are present when LDAP auth is enabled.
 */
final class LdapConfiguration
{
	public readonly string $host;
	public readonly int $port;
	public readonly string $base_dn;
	public readonly string $bind_dn;
	public readonly string $bind_password;
	public readonly string $user_filter;
	public readonly string $attr_username;
	public readonly string $attr_email;
	public readonly string $attr_display_name;
	public readonly ?string $admin_group_dn;
	public readonly bool $auto_provision;
	public readonly bool $use_tls;
	public readonly bool $tls_verify_peer;
	public readonly int $connection_timeout;

	/**
	 * @throws \InvalidArgumentException if required LDAP config missing
	 */
	public function __construct()
	{
		$connection = Config::get('ldap.connections.default');
		$auth = Config::get('ldap.auth');

		// Validate required connection settings
		$this->host = $this->requireString($connection['hosts'][0] ?? null, 'LDAP_HOST');
		$this->port = $this->requireInt($connection['port'] ?? null, 'LDAP_PORT');
		$this->base_dn = $this->requireString($connection['base_dn'] ?? null, 'LDAP_BASE_DN');
		$this->bind_dn = $this->requireString($connection['username'] ?? null, 'LDAP_BIND_DN');
		$this->bind_password = $this->requireString($connection['password'] ?? null, 'LDAP_BIND_PASSWORD');
		$this->connection_timeout = $connection['timeout'] ?? 5;
		$this->use_tls = $connection['use_tls'] ?? true;

		// Extract TLS verification from options array
		$optTlsRequireCert = $connection['options'][LDAP_OPT_X_TLS_REQUIRE_CERT] ?? LDAP_OPT_X_TLS_DEMAND;
		$this->tls_verify_peer = $optTlsRequireCert === LDAP_OPT_X_TLS_DEMAND;

		// Validate authentication settings
		$this->user_filter = $this->requireString($auth['user_filter'] ?? null, 'LDAP_USER_FILTER');
		$this->attr_username = $this->requireString($auth['attributes']['username'] ?? null, 'LDAP_ATTR_USERNAME');
		$this->attr_email = $this->requireString($auth['attributes']['email'] ?? null, 'LDAP_ATTR_EMAIL');
		$this->attr_display_name = $this->requireString($auth['attributes']['display_name'] ?? null, 'LDAP_ATTR_DISPLAY_NAME');

		// Optional settings
		$this->admin_group_dn = $auth['admin_group_dn'] ?? null;
		$this->auto_provision = $auth['auto_provision'] ?? true;
	}

	/**
	 * Validate that a string configuration value is present.
	 *
	 * @param mixed  $value   The configuration value
	 * @param string $envName Environment variable name for error messages
	 *
	 * @throws \InvalidArgumentException if value is null or empty
	 */
	private function requireString(mixed $value, string $envName): string
	{
		if ($value === null || $value === '') {
			throw new \InvalidArgumentException("LDAP configuration error: {$envName} is required but not set");
		}

		return (string) $value;
	}

	/**
	 * Validate that an integer configuration value is present and valid.
	 *
	 * @param mixed  $value   The configuration value
	 * @param string $envName Environment variable name for error messages
	 *
	 * @throws \InvalidArgumentException if value is null or not a valid integer
	 */
	private function requireInt(mixed $value, string $envName): int
	{
		if ($value === null || !is_numeric($value)) {
			throw new \InvalidArgumentException("LDAP configuration error: {$envName} is required but not set or invalid");
		}

		return (int) $value;
	}
}
