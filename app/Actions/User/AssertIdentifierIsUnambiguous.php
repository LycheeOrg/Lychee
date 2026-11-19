<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\User;

use App\Exceptions\ConflictingPropertyException;
use App\Models\Builders\UserBuilder;
use App\Models\User;
use App\Repositories\ConfigManager;

/**
 * Reject a username or email that would make a login identifier address two accounts.
 *
 * With `email_login_enabled` a login identifier is matched against both
 * `username` and `email`, so the two attributes share one namespace: a
 * username that doubles as another account's email (or the reverse) makes
 * that identifier ambiguous. `AuthController` resolves such a collision in
 * favour of the username, which silently costs the other account its email
 * login, so the collision is refused whenever an identifier is written.
 *
 * Only checked while the setting is on: with it off the two attributes are
 * independent and an overlap is harmless.
 */
class AssertIdentifierIsUnambiguous
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * @param string   $username username to be stored
	 * @param int|null $user_id  id of the account being updated, which may reuse its own email
	 *
	 * @throws ConflictingPropertyException if the username is already another account's email
	 */
	public function username(string $username, ?int $user_id = null): void
	{
		if (!$this->config_manager->getValueAsBool('email_login_enabled')) {
			return;
		}

		if ($this->otherUsers($user_id)->where('email', '=', $username)->count() !== 0) {
			throw new ConflictingPropertyException('Username is already used as an email address');
		}
	}

	/**
	 * @param string|null $email   email to be stored
	 * @param int|null    $user_id id of the account being updated, which may reuse its own username
	 *
	 * @throws ConflictingPropertyException if the email is already another account's username
	 */
	public function email(?string $email, ?int $user_id = null): void
	{
		if ($email === null || !$this->config_manager->getValueAsBool('email_login_enabled')) {
			return;
		}

		if ($this->otherUsers($user_id)->where('username', '=', $email)->count() !== 0) {
			throw new ConflictingPropertyException('email is already used as a username');
		}
	}

	private function otherUsers(?int $user_id): UserBuilder
	{
		$query = User::query();
		if ($user_id !== null) {
			$query->where('id', '!=', $user_id);
		}

		return $query;
	}
}
