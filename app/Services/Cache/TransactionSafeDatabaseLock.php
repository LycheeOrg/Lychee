<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Cache;

use Illuminate\Cache\DatabaseLock;

/**
 * Lock of the `database` cache store that runs no failing statement.
 *
 * Laravel's {@see DatabaseLock} inserts the lock row and, when the key is
 * already taken, catches the unique-constraint violation and updates the row
 * instead. PostgreSQL aborts the surrounding transaction on that violation:
 * the update and every later statement of the transaction fail. Here the
 * insert ignores an existing key (`ON CONFLICT DO NOTHING`, `INSERT IGNORE`,
 * `INSERT OR IGNORE`), so a lock can be taken inside an open transaction
 * (ADR-090-01).
 */
class TransactionSafeDatabaseLock extends DatabaseLock
{
	public function acquire(): bool
	{
		$acquired = $this->insertRow() || $this->takeOverRow();

		if (count($this->lottery ?? []) === 2 && random_int(1, $this->lottery[1]) <= $this->lottery[0]) {
			$this->pruneExpiredLocks();
		}

		return $acquired;
	}

	/**
	 * Insert the lock row unless the key exists.
	 */
	private function insertRow(): bool
	{
		return $this->connection->table($this->table)->insertOrIgnore([
			'key' => $this->name,
			'owner' => $this->owner,
			'expiration' => $this->expiresAt(),
		]) === 1;
	}

	/**
	 * Take the existing row over when this owner holds it or it expired.
	 */
	private function takeOverRow(): bool
	{
		return $this->connection->table($this->table)
			->where('key', '=', $this->name)
			->where(fn ($query) => $query->where('owner', '=', $this->owner)->orWhere('expiration', '<=', $this->currentTime()))
			->update([
				'owner' => $this->owner,
				'expiration' => $this->expiresAt(),
			]) >= 1;
	}
}
