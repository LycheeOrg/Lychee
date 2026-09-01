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

namespace Tests\Unit\Rules;

use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Rules\ChunkSequenceRule;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Tests\AbstractTestCase;

class ChunkSequenceRuleTest extends AbstractTestCase
{
	private const UUID_NAME = 'AbCdEfGh12345678.png';

	/** Mirrors the rule's private lock-key format, used only to probe mutex state from the outside. */
	private function lockKey(): string
	{
		return 'photo-upload-lock:' . self::UUID_NAME;
	}

	/** Mirrors the rule's private cache-key format, used only to inspect recorded progress from the outside. */
	private function cacheKey(): string
	{
		return 'photo-upload-chunk:' . self::UUID_NAME;
	}

	public function testValidateThrowsForWrongAttribute(): void
	{
		$this->expectException(LycheeLogicException::class);

		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);
		$rule->validate('not_uuid_name', self::UUID_NAME, function (): void {
		});
	}

	public function testValidateIsNoopForFirstOrMissingChunkNumber(): void
	{
		foreach ([0, 1] as $chunk_number) {
			$rule = new ChunkSequenceRule();
			$rule->setData(['chunk_number' => $chunk_number]);
			$fail_called = false;
			$rule->validate('uuid_name', null, function () use (&$fail_called): void {
				$fail_called = true;
			});
			self::assertFalse($fail_called, "chunk_number={$chunk_number} must be a no-op");
		}
	}

	public function testValidateIsNoopWhenUuidNameIsEmptyString(): void
	{
		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);
		$fail_called = false;
		$rule->validate('uuid_name', '', function () use (&$fail_called): void {
			$fail_called = true;
		});
		self::assertFalse($fail_called);
	}

	public function testValidateIsNoopWhenValueIsNotAString(): void
	{
		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);
		$fail_called = false;
		$rule->validate('uuid_name', 12345, function () use (&$fail_called): void {
			$fail_called = true;
		});
		self::assertFalse($fail_called);
	}

	public function testValidatePassesAndHoldsLockWhenSequenceMatches(): void
	{
		// Simulate chunk 1 having already been appended.
		(new ChunkSequenceRule())->completeAppend(self::UUID_NAME, 1, false);

		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);
		$fail_called = false;
		$rule->validate('uuid_name', self::UUID_NAME, function () use (&$fail_called): void {
			$fail_called = true;
		});
		self::assertFalse($fail_called);

		// The mutex must still be held: a fresh, non-blocking lock attempt must fail.
		$probe = Cache::lock($this->lockKey(), 1);
		self::assertFalse($probe->get());

		// Completing the append must record progress and release the mutex.
		$rule->completeAppend(self::UUID_NAME, 2, false);
		self::assertSame(2, Cache::get($this->cacheKey()));

		$probe = Cache::lock($this->lockKey(), 1);
		self::assertTrue($probe->get());
		$probe->release();
	}

	public function testValidateThrowsConflictWhenChunkAlreadyReceived(): void
	{
		// Chunk 2 was already recorded; resubmitting chunk_number=2 is a replay.
		(new ChunkSequenceRule())->completeAppend(self::UUID_NAME, 2, false);

		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);

		try {
			$rule->validate('uuid_name', self::UUID_NAME, function (): void {
			});
			self::fail('Expected ConflictingPropertyException was not thrown.');
		} catch (ConflictingPropertyException $e) {
			self::assertStringContainsString('already been received', $e->getMessage());
		}

		// The mutex must not be left held after a rejected replay.
		$probe = Cache::lock($this->lockKey(), 1);
		self::assertTrue($probe->get());
		$probe->release();
	}

	public function testValidateThrowsConflictWhenChunkIsOutOfOrder(): void
	{
		// No progress recorded yet, so chunk_number=3 skips ahead of the expected chunk_number=1.
		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 3]);

		try {
			$rule->validate('uuid_name', self::UUID_NAME, function (): void {
			});
			self::fail('Expected ConflictingPropertyException was not thrown.');
		} catch (ConflictingPropertyException $e) {
			self::assertStringContainsString('out of order', $e->getMessage());
		}

		$probe = Cache::lock($this->lockKey(), 1);
		self::assertTrue($probe->get());
		$probe->release();
	}

	public function testValidateThrowsConflictOnLockTimeout(): void
	{
		$lock = \Mockery::mock(Lock::class);
		$lock->shouldReceive('block')
			->once()
			->andThrow(new LockTimeoutException());

		Cache::shouldReceive('lock')
			->once()
			->with($this->lockKey(), 30)
			->andReturn($lock);

		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);

		$this->expectException(ConflictingPropertyException::class);
		$rule->validate('uuid_name', self::UUID_NAME, function (): void {
		});
	}

	public function testCompleteAppendForgetsProgressOnLastChunk(): void
	{
		$rule = new ChunkSequenceRule();
		$rule->completeAppend(self::UUID_NAME, 1, false);
		self::assertSame(1, Cache::get($this->cacheKey()));

		$rule = new ChunkSequenceRule();
		$rule->completeAppend(self::UUID_NAME, 2, true);

		self::assertNull(Cache::get($this->cacheKey()));
	}

	public function testReleaseWithoutCommitReleasesLockAndDoesNotRecordProgress(): void
	{
		(new ChunkSequenceRule())->completeAppend(self::UUID_NAME, 1, false);

		$rule = new ChunkSequenceRule();
		$rule->setData(['chunk_number' => 2]);
		$rule->validate('uuid_name', self::UUID_NAME, function (): void {
		});

		$rule->releaseWithoutCommit();

		// No progress must have been recorded for the still-in-flight chunk 2.
		self::assertSame(1, Cache::get($this->cacheKey()));

		$probe = Cache::lock($this->lockKey(), 1);
		self::assertTrue($probe->get());
		$probe->release();
	}

	public function testReleaseWithoutCommitIsNoopWhenNoLockHeld(): void
	{
		$rule = new ChunkSequenceRule();
		$rule->releaseWithoutCommit();

		self::assertNull(Cache::get($this->cacheKey()));
	}
}
