<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Cache;

use Illuminate\Cache\DatabaseStore;

/**
 * `database` cache store whose locks are {@see TransactionSafeDatabaseLock}s
 * (ADR-090-01), and which can delete its expired rows by key prefix (ADR-116-01).
 */
class TransactionSafeDatabaseStore extends DatabaseStore
{
	/**
	 * Delete the expired rows whose key starts with the given prefix.
	 *
	 * Laravel removes an expired row only when its key is read again.
	 * The `like` pattern is not escaped: `_` and `%` in the prefix only widen
	 * the match among rows that have already expired.
	 *
	 * @return int number of deleted rows
	 */
	public function forgetExpired(string $key_prefix): int
	{
		return $this->table()
			->where('key', 'like', $this->prefix . $key_prefix . '%')
			->where('expiration', '<=', $this->currentTime())
			->delete();
	}

	/**
	 * @param string      $name
	 * @param int         $seconds
	 * @param string|null $owner
	 */
	public function lock($name, $seconds = 0, $owner = null): TransactionSafeDatabaseLock
	{
		return new TransactionSafeDatabaseLock(
			$this->lockConnection ?? $this->connection,
			$this->lockTable,
			$this->prefix . $name,
			$seconds,
			$owner,
			$this->lockLottery,
			$this->defaultLockTimeoutInSeconds
		);
	}
}
