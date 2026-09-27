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

use App\Services\GalleryLockState;
use Illuminate\Support\Facades\Hash;
use Tests\AbstractTestCase;

class GalleryLockStateTest extends AbstractTestCase
{
	private const STORED_HASH = '$2y$12$abcdefghijklmnopqrstuuFakeBcryptHashForUnitTestsOnly00';
	private const NOW = 1_800_000_000;

	private GalleryLockState $gallery_lock;

	public function setUp(): void
	{
		parent::setUp();
		$this->gallery_lock = resolve(GalleryLockState::class);
		Hash::shouldReceive('check')->never();
	}

	public function testUnlockedWhenNoPasswordIsSet(): void
	{
		self::assertFalse($this->gallery_lock->isLocked('', false, null, self::NOW));
	}

	public function testUnlockedForLoggedInUser(): void
	{
		self::assertFalse($this->gallery_lock->isLocked(self::STORED_HASH, true, null, self::NOW));
	}

	public function testLockedWithoutCookie(): void
	{
		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, null, self::NOW));
	}

	public function testUnlockedWithMatchingCookie(): void
	{
		$cookie = $this->gallery_lock->makeCookieValue(self::STORED_HASH, self::NOW + 60);

		self::assertFalse($this->gallery_lock->isLocked(self::STORED_HASH, false, $cookie, self::NOW));
	}

	public function testUnlockedWithMatchingBrowserSessionCookie(): void
	{
		$cookie = $this->gallery_lock->makeCookieValue(self::STORED_HASH, null);

		self::assertFalse($this->gallery_lock->isLocked(self::STORED_HASH, false, $cookie, self::NOW));
	}

	public function testLockedWithStaleFingerprint(): void
	{
		$cookie = $this->gallery_lock->makeCookieValue('$2y$12$aDifferentHashFromAPreviousPasswordxxxxxxxxxxxxxxxxxxx', self::NOW + 60);

		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, $cookie, self::NOW));
	}

	public function testLockedWithExpiredCookie(): void
	{
		$cookie = $this->gallery_lock->makeCookieValue(self::STORED_HASH, self::NOW - 1);

		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, $cookie, self::NOW));
	}

	public function testLockedWithMalformedCookie(): void
	{
		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, 'not-json', self::NOW));
		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, '{"exp":null}', self::NOW));
		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, '{"f":42,"exp":null}', self::NOW));
		self::assertTrue($this->gallery_lock->isLocked(self::STORED_HASH, false, '{"f":"abc","exp":"soon"}', self::NOW));
	}

	public function testFingerprintIsKeyedHmacSha3(): void
	{
		$expected = hash_hmac('sha3-256', self::STORED_HASH, (string) config('app.key'));

		self::assertSame($expected, $this->gallery_lock->fingerprint(self::STORED_HASH));
		self::assertNotSame(hash('sha3-256', self::STORED_HASH), $this->gallery_lock->fingerprint(self::STORED_HASH));
	}
}
