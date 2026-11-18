<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Services;

use App\DTO\UploadStatusEntry;
use App\Enum\UploadStatus;
use App\Services\Cache\TransactionSafeDatabaseStore;
use App\Services\UploadStatusStore;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;
use Tests\AbstractTestCase;

/**
 * Feature 116: upload status entries in the shared cache (FR-116-08, FR-116-10, NFR-116-03, S-116-17, S-116-20).
 */
class UploadStatusStoreTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private const ID_A = 'aaaaaaaaaaaaaaaaaaaaaaaa';
	private const ID_B = 'bbbbbbbbbbbbbbbbbbbbbbbb';
	private const ID_C = 'cccccccccccccccccccccccc';

	private UploadStatusStore $store;

	protected function setUp(): void
	{
		parent::setUp();
		Cache::store('array')->flush();
		$this->store = resolve(UploadStatusStore::class);
	}

	protected function tearDown(): void
	{
		Lottery::determineResultsNormally();
		parent::tearDown();
	}

	public function testRecordedEntryIsReadBack(): void
	{
		$entry = UploadStatusEntry::ofPhoto(7, self::ID_A, is_duplicate: false);

		$this->store->record(self::ID_A, $entry);

		self::assertEquals([$entry], $this->store->many([self::ID_A]));
		self::assertSame($entry->toArray(), Cache::store('array')->get(UploadStatusStore::KEY_PREFIX . self::ID_A));
	}

	public function testManyKeepsRequestOrderAndGivesNullForMissingOrGarbage(): void
	{
		$a = UploadStatusEntry::processing(7);
		$c = UploadStatusEntry::ofPhoto(7, self::ID_C, is_duplicate: true);
		$this->store->record(self::ID_A, $a);
		$this->store->record(self::ID_C, $c);
		Cache::store('array')->put(UploadStatusStore::KEY_PREFIX . self::ID_B, 'garbage', 60);

		self::assertEquals([$c, null, $a, $c], $this->store->many([self::ID_C, self::ID_B, self::ID_A, self::ID_C]));
	}

	public function testManyOfNothingIsEmpty(): void
	{
		self::assertSame([], $this->store->many([]));
	}

	public function testEntryExpiresAfterOneDay(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::processing(7));

		$this->travel(UploadStatusStore::TTL - 10)->seconds();
		self::assertNotNull($this->store->many([self::ID_A])[0]);

		$this->travel(20)->seconds();
		self::assertNull($this->store->many([self::ID_A])[0]);
	}

	public function testEachWriteResetsTheExpiry(): void
	{
		$this->store->record(self::ID_A, UploadStatusEntry::processing(7));
		$this->travel(UploadStatusStore::TTL - 10)->seconds();
		$this->store->record(self::ID_A, UploadStatusEntry::ofPhoto(7, self::ID_A, is_duplicate: false));
		$this->travel(UploadStatusStore::TTL - 10)->seconds();

		self::assertSame(UploadStatus::COMPLETED, $this->store->many([self::ID_A])[0]?->status);
	}

	public function testCacheErrorIsLoggedNotThrown(): void
	{
		Cache::shouldReceive('store')->andThrow(new \RuntimeException('cache down'));
		Log::shouldReceive('warning')
			->once()
			->withArgs(fn (string $message) => str_contains($message, self::ID_A) && str_contains($message, 'cache down'));

		$this->store->record(self::ID_A, UploadStatusEntry::processing(7));
	}

	public function testAsyncQueueOnArrayCacheWritesToTheDatabaseStore(): void
	{
		$this->useDatabaseStore();

		$this->store->record(self::ID_A, UploadStatusEntry::processing(7));

		self::assertNull(Cache::store('array')->get(UploadStatusStore::KEY_PREFIX . self::ID_A));
		self::assertSame(UploadStatus::PROCESSING, $this->store->many([self::ID_A])[0]?->status);
	}

	public function testProcessingWriteOnDatabaseStorePrunesExpiredUploadStatuses(): void
	{
		$this->useDatabaseStore();
		$this->seedCacheRows();
		Lottery::alwaysWin();

		$this->store->record(self::ID_A, UploadStatusEntry::processing(7));

		self::assertSame([
			$this->fullKey('other:expired'),
			$this->fullKey(UploadStatusStore::KEY_PREFIX . self::ID_A),
			$this->fullKey(UploadStatusStore::KEY_PREFIX . 'live'),
		], $this->cacheKeys());
	}

	public function testLostLotteryDoesNotPrune(): void
	{
		$this->useDatabaseStore();
		$this->seedCacheRows();
		Lottery::alwaysLose();

		$this->store->record(self::ID_A, UploadStatusEntry::processing(7));

		self::assertContains($this->fullKey(UploadStatusStore::KEY_PREFIX . 'expired'), $this->cacheKeys());
	}

	public function testOutcomeWriteDoesNotPrune(): void
	{
		$this->useDatabaseStore();
		$this->seedCacheRows();
		Lottery::alwaysWin();

		$this->store->record(self::ID_A, UploadStatusEntry::ofPhoto(7, self::ID_A, is_duplicate: false));

		self::assertContains($this->fullKey(UploadStatusStore::KEY_PREFIX . 'expired'), $this->cacheKeys());
	}

	private function useDatabaseStore(): void
	{
		Config::set('queue.default', 'database');
		Config::set('cache.default', 'array');
	}

	private function seedCacheRows(): void
	{
		$past = now()->getTimestamp() - 10;
		$future = now()->getTimestamp() + 3600;
		DB::table('cache')->insert([
			['key' => $this->fullKey(UploadStatusStore::KEY_PREFIX . 'expired'), 'value' => 'x', 'expiration' => $past],
			['key' => $this->fullKey(UploadStatusStore::KEY_PREFIX . 'live'), 'value' => 'x', 'expiration' => $future],
			['key' => $this->fullKey('other:expired'), 'value' => 'x', 'expiration' => $past],
		]);
	}

	private function fullKey(string $key): string
	{
		/** @var TransactionSafeDatabaseStore $store */
		$store = Cache::store('database')->getStore();

		return $store->getPrefix() . $key;
	}

	/**
	 * @return string[]
	 */
	private function cacheKeys(): array
	{
		/** @var string[] $keys */
		$keys = DB::table('cache')->orderBy('key')->pluck('key')->all();

		return $keys;
	}
}
