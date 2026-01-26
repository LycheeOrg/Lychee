<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Auth;

use App\DTO\LdapConfiguration;
use App\DTO\LdapUser;
use Illuminate\Support\Facades\Log;
use LdapRecord\Connection;
use LdapRecord\Container;

/**
 * LDAP authentication service (wrapper over LdapRecord).
 *
 * Provides LDAP authentication with search-first pattern:
 * 1. Search for user by username to get DN
 * 2. Bind with user DN and password
 * 3. Retrieve user attributes
 *
 * Group membership queries are separate public methods called by ProvisionLdapUser.
 */
class LdapService
{
	private Connection $connection;

	public function __construct(
		private readonly LdapConfiguration $config,
	) {
	}

	/**
	 * Authenticate user via LDAP.
	 *
	 * Search-first pattern: searches for username, gets DN, binds with DN+password.
	 * Returns minimal LdapUser on success, null on failure.
	 *
	 * @param string $username LDAP username (uid or sAMAccountName)
	 * @param string $password User's password
	 *
	 * @return LdapUser|null User data on success, null on authentication failure
	 */
	public function authenticate(string $username, string $password): ?LdapUser
	{
		Log::debug('LDAP authentication attempt', [
			'username' => $username,
			'host' => $this->config->host,
		]);

		try {
			// Step 1: Connect to LDAP server
			$this->connect();
			Log::debug('LDAP connection established', ['host' => $this->config->host]);

			// Step 2: Search for user to get DN
			$userDn = $this->searchUser($username);
			if ($userDn === null) {
				Log::notice('LDAP user not found', ['username' => $username]);

				return null;
			}
			Log::debug('LDAP user found', ['username' => $username, 'dn' => $userDn]);

			// Step 3: Bind with user DN and password
			$this->connection->auth()->attempt($userDn, $password);
			Log::debug('LDAP bind successful', ['dn' => $userDn]);

			// Step 4: Retrieve user attributes (email, display_name)
			$attributes = $this->retrieveAttributes($userDn);
			Log::debug('LDAP attributes retrieved', [
				'dn' => $userDn,
				'has_email' => $attributes['email'] !== null,
				'has_display_name' => $attributes['display_name'] !== null,
			]);

			// Step 5: Return LdapUser with attributes
			return new LdapUser(
				username: $username,
				userDn: $userDn,
				email: $attributes['email'],
				display_name: $attributes['display_name']
			);
		} catch (\Throwable $e) {
			// Authentication failed (bind error, connection error, etc.)
			Log::warning('LDAP authentication failed', [
				'username' => $username,
				'error' => $e->getMessage(),
				'exception' => get_class($e),
			]);

			return null;
		}
	}

	/**
	 * Query user's LDAP group memberships.
	 *
	 * Called by ProvisionLdapUser during user creation/update, NOT during authentication.
	 *
	 * @param string $userDn User's Distinguished Name
	 *
	 * @return array<string> Array of group DNs
	 */
	public function queryGroups(string $userDn): array
	{
		Log::debug('LDAP querying user groups', ['dn' => $userDn]);

		try {
			// Ensure connected
			if (!isset($this->connection)) {
				$this->connect();
			}

			// Search for groups where user is a member
			// Standard LDAP filter: (member=USER_DN)
			$results = $this->connection->query()
				->setDn($this->config->base_dn)
				->rawFilter("(member={$userDn})")
				->get();

			// Extract DNs from results
			$groupDns = [];
			foreach ($results as $group) {
				$groupDns[] = $group->getDn();
			}

			Log::debug('LDAP groups retrieved', [
				'dn' => $userDn,
				'group_count' => count($groupDns),
			]);

			return $groupDns;
		} catch (\Throwable $e) {
			// Group query failed, return empty array
			Log::warning('LDAP group query failed', [
				'dn' => $userDn,
				'error' => $e->getMessage(),
			]);

			return [];
		}
	}

	/**
	 * Check if user is in admin group.
	 *
	 * @param array<string> $groupDns User's group DNs from queryGroups()
	 *
	 * @return bool True if user in admin group, false otherwise (default non-admin)
	 */
	public function isUserInAdminGroup(array $groupDns): bool
	{
		// If no admin group configured, all users are non-admin
		if ($this->config->admin_group_dn === null) {
			Log::debug('LDAP admin group not configured');

			return false;
		}

		// Check if admin group DN is in user's group DNs
		// Use case-insensitive comparison (LDAP DNs are case-insensitive)
		foreach ($groupDns as $groupDn) {
			if (strcasecmp($groupDn, $this->config->admin_group_dn) === 0) {
				Log::info('LDAP user is admin', [
					'admin_group_dn' => $this->config->admin_group_dn,
				]);

				return true;
			}
		}

		Log::debug('LDAP user is not admin', [
			'admin_group_dn' => $this->config->admin_group_dn,
			'user_groups' => count($groupDns),
		]);

		return false;
	}

	/**
	 * Establish LDAP connection with TLS enforcement.
	 *
	 * @throws \LdapRecord\Auth\BindException if connection fails
	 */
	private function connect(): void
	{
		if (isset($this->connection)) {
			return; // Already connected
		}

		// Create connection from config
		$this->connection = new Connection([
			'hosts' => [$this->config->host],
			'port' => $this->config->port,
			'base_dn' => $this->config->base_dn,
			'username' => $this->config->bind_dn,
			'password' => $this->config->bind_password,
			'timeout' => $this->config->connection_timeout,
			'use_tls' => $this->config->use_tls,
			'options' => [
				LDAP_OPT_X_TLS_REQUIRE_CERT => $this->config->tls_verify_peer
					? LDAP_OPT_X_TLS_DEMAND
					: LDAP_OPT_X_TLS_ALLOW,
			],
		]);

		// Add connection to container for LdapRecord to use
		Container::addConnection($this->connection, 'default');

		// Bind with service account
		$this->connection->connect();
	}

	/**
	 * Search for user by username and return DN.
	 *
	 * @param string $username LDAP username (uid or sAMAccountName)
	 *
	 * @return string|null User DN if found, null otherwise
	 */
	private function searchUser(string $username): ?string
	{
		// Build search filter (replace %s with username)
		$filter = str_replace('%s', $username, $this->config->user_filter);

		// Search for user
		$results = $this->connection->query()
			->setDn($this->config->base_dn)
			->rawFilter($filter)
			->limit(1)
			->get();

		if ($results->count() === 0) {
			return null;
		}

		// Return DN of first match
		return $results->first()->getDn();
	}

	/**
	 * Retrieve user attributes after successful bind.
	 *
	 * @param string $userDn User's Distinguished Name
	 *
	 * @return array{email: string|null, display_name: string|null} User attributes
	 */
	private function retrieveAttributes(string $userDn): array
	{
		// Query LDAP for user entry by DN
		$result = $this->connection->query()
			->setDn($userDn)
			->read()
			->first();

		if ($result === null) {
			return ['email' => null, 'display_name' => null];
		}

		// Extract configured attributes (LDAP attributes are arrays, get first value)
		$emailAttr = $this->config->attr_email;
		$displayNameAttr = $this->config->attr_display_name;

		// Get first value from LDAP multi-value attributes
		$emailValues = $result->getAttribute($emailAttr);
		$displayNameValues = $result->getAttribute($displayNameAttr);

		return [
			'email' => is_array($emailValues) && count($emailValues) > 0 ? $emailValues[0] : null,
			'display_name' => is_array($displayNameValues) && count($displayNameValues) > 0 ? $displayNameValues[0] : null,
		];
	}
}
