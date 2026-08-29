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

namespace Tests\Unit\Services\Cache;

use App\Services\Cache\CacheKeyProvider;
use Tests\AbstractTestCase;

/**
 * Covers Feature 057 T-057-02: {@see CacheKeyProvider::albumListingV3Tag()}
 * and {@see CacheKeyProvider::albumListingV3Key()}.
 */
class CacheKeyProviderTest extends AbstractTestCase
{
	private CacheKeyProvider $provider;

	protected function setUp(): void
	{
		parent::setUp();
		$this->provider = new CacheKeyProvider();
	}

	public function testAlbumListingV3TagIsStable(): void
	{
		self::assertSame('album-listing-v3', $this->provider->albumListingV3Tag());
	}

	/**
	 * NFR-057-04: no two distinct (user identity, with_parent_id,
	 * for_bulk_edit) combinations may collide on the same key.
	 */
	public function testAlbumListingV3KeyIsUniqueAcrossIdentityAndFlagMatrix(): void
	{
		$user_ids = [null, 'user-a', 'user-b'];
		$flag_combinations = [
			[false, false],
			[true, false],
			[false, true],
			[true, true],
		];

		$keys = [];
		foreach ($user_ids as $user_id) {
			foreach ($flag_combinations as [$with_parent_id, $for_bulk_edit]) {
				$key = $this->provider->albumListingV3Key($user_id, $with_parent_id, $for_bulk_edit);
				self::assertArrayNotHasKey($key, $keys, 'duplicate key generated for user_id=' . var_export($user_id, true) . ', with_parent_id=' . var_export($with_parent_id, true) . ', for_bulk_edit=' . var_export($for_bulk_edit, true));
				$keys[$key] = true;
			}
		}

		self::assertCount(12, $keys);
	}

	public function testAlbumListingV3KeyIsDeterministic(): void
	{
		$key1 = $this->provider->albumListingV3Key('user-a', true, false);
		$key2 = $this->provider->albumListingV3Key('user-a', true, false);

		self::assertSame($key1, $key2);
	}

	/**
	 * @param callable(string,int|string|null):string $key_fn
	 */
	private function assertUniqueAcrossIdentityAndAlbumMatrix(callable $key_fn): void
	{
		$user_ids = [null, 'user-a', 'user-b'];
		$album_ids = ['album-1', 'album-2'];

		$keys = [];
		foreach ($album_ids as $album_id) {
			foreach ($user_ids as $user_id) {
				$key = $key_fn($album_id, $user_id);
				self::assertArrayNotHasKey($key, $keys, 'duplicate key for album_id=' . $album_id . ', user_id=' . var_export($user_id, true));
				$keys[$key] = true;
			}
		}

		self::assertCount(6, $keys);
	}

	/**
	 * Feature 061, NFR-061-05.
	 */
	public function testAlbumBucketsKeyIsUniqueAcrossIdentityAndAlbumMatrix(): void
	{
		$this->assertUniqueAcrossIdentityAndAlbumMatrix(fn (string $album_id, int|string|null $user_id) => $this->provider->albumBucketsKey($album_id, $user_id));
	}

	/**
	 * Feature 061, NFR-061-05.
	 */
	public function testAlbumChildrenDataKeyIsUniqueAcrossIdentityAndAlbumMatrix(): void
	{
		$this->assertUniqueAcrossIdentityAndAlbumMatrix(fn (string $album_id, int|string|null $user_id) => $this->provider->albumChildrenDataKey($album_id, $user_id));
	}

	/**
	 * Feature 061, NFR-061-05.
	 */
	public function testAlbumChildrenRightsKeyIsUniqueAcrossIdentityAndAlbumMatrix(): void
	{
		$this->assertUniqueAcrossIdentityAndAlbumMatrix(fn (string $album_id, int|string|null $user_id) => $this->provider->albumChildrenRightsKey($album_id, $user_id));
	}
}
