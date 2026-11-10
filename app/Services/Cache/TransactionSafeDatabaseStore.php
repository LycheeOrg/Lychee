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
 * (ADR-090-01).
 */
class TransactionSafeDatabaseStore extends DatabaseStore
{
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
