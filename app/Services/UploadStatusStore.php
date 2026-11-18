<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\DTO\UploadStatusEntry;
use App\Enum\UploadStatus;
use App\Services\Cache\SharedCacheStore;
use App\Services\Cache\TransactionSafeDatabaseStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;

/**
 * Upload statuses kept for one day under their provisional ID (Feature 116, ADR-116-01).
 *
 * Recording never fails the caller: a cache error is logged (NFR-116-03).
 */
class UploadStatusStore
{
	public const KEY_PREFIX = 'upload-status:';

	/** One day, reset on each write (FR-116-08). */
	public const TTL = 86400;

	/** A `processing` write prunes expired rows of a `database` store once in this many (FR-116-10). */
	public const PRUNE_ODDS = 50;

	public function __construct(private readonly SharedCacheStore $shared_cache_store)
	{
	}

	public function record(string $provisional_id, UploadStatusEntry $entry): void
	{
		try {
			$repository = $this->shared_cache_store->repository();
			$repository->put(self::KEY_PREFIX . $provisional_id, $entry->toArray(), self::TTL);
			$this->mayPrune($repository, $entry->status);
		} catch (\Throwable $e) {
			Log::warning(sprintf('Upload status of %s not recorded: %s', $provisional_id, $e->getMessage()));
		}
	}

	/**
	 * @param string[] $provisional_ids
	 *
	 * @return array<int,UploadStatusEntry|null> one item per ID, in the given order
	 */
	public function many(array $provisional_ids): array
	{
		if (count($provisional_ids) === 0) {
			return [];
		}

		$keys = array_map(fn (string $id) => self::KEY_PREFIX . $id, $provisional_ids);
		$values = $this->shared_cache_store->repository()->many(array_values(array_unique($keys)));

		return array_map(fn (string $key) => UploadStatusEntry::fromArray($values[$key] ?? null), array_values($keys));
	}

	private function mayPrune(Repository $repository, UploadStatus $status): void
	{
		$store = $repository->getStore();
		if ($status !== UploadStatus::PROCESSING || !$store instanceof TransactionSafeDatabaseStore) {
			return;
		}

		Lottery::odds(1, self::PRUNE_ODDS)
			->winner(fn () => $store->forgetExpired(self::KEY_PREFIX))
			->choose();
	}
}
