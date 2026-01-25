<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * LDAP user data transfer object.
 *
 * Represents the minimal user information retrieved from LDAP during authentication.
 * Groups are NOT included here - they are queried separately during provisioning.
 */
final class LdapUser
{
	/**
	 * @param string      $username     Unique LDAP identifier (uid/sAMAccountName) for login
	 * @param string      $userDn       Distinguished Name from LDAP search
	 * @param string|null $email        Email address (nullable if LDAP attribute missing)
	 * @param string|null $display_name User-friendly name (nullable, fallback to username during provisioning)
	 */
	public function __construct(
		public readonly string $username,
		public readonly string $userDn,
		public readonly ?string $email = null,
		public readonly ?string $display_name = null,
	) {
	}
}
