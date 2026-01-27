<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Exceptions\LdapConfigurationException;
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
	 * @throws LdapConfigurationException if required LDAP config missing
	 */
	public function __construct()
	{
		// Validate required connection settings
		$this->host = $this->requireString(config('ldap.connections.default.hosts')[0], 'LDAP_HOST', 'ldap.example.com');
		$this->port = $this->requireInt(config('ldap.connections.default.port'), 'LDAP_PORT');
		$this->base_dn = $this->requireString(config('ldap.connections.default.base_dn'), 'LDAP_BASE_DN', 'dc=example,dc=com');
		$this->bind_dn = $this->requireString(config('ldap.connections.default.username'), 'LDAP_BIND_DN', 'cn=bind-user,dc=example,dc=com');
		$this->bind_password = $this->requireString(config('ldap.connections.default.password'), 'LDAP_BIND_PASSWORD');
		$this->connection_timeout = config('ldap.connections.default.timeout', 5);
		$this->use_tls = config('ldap.connections.default.use_tls', true);

		// Extract TLS verification from options array
		$opt_tls_require_cert = config('ldap.connections.default.options')[LDAP_OPT_X_TLS_REQUIRE_CERT] ?? LDAP_OPT_X_TLS_DEMAND;
		$this->tls_verify_peer = $opt_tls_require_cert === LDAP_OPT_X_TLS_DEMAND;

		// Validate authentication settings
		$auth = Config::get('ldap.auth');
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
	 * @param mixed  $value    The configuration value
	 * @param string $env_name Environment variable name for error messages
	 * @param string $default  Default value if not set
	 *
	 * @throws LdapConfigurationException if value is null or empty
	 */
	private function requireString(mixed $value, string $env_name, string $default = ''): string
	{
		if ($value === null || $value === '' || $value === $default) {
			throw new LdapConfigurationException("LDAP configuration error: {$env_name} is required but not set");
		}

		return (string) $value;
	}

	/**
	 * Validate that an integer configuration value is present and valid.
	 *
	 * @param mixed  $value    The configuration value
	 * @param string $env_name Environment variable name for error messages
	 *
	 * @throws LdapConfigurationException if value is null or not a valid integer
	 */
	private function requireInt(mixed $value, string $env_name): int
	{
		if ($value === null || !is_numeric($value)) {
			throw new LdapConfigurationException("LDAP configuration error: {$env_name} is required but not set or invalid");
		}

		return (int) $value;
	}
}
