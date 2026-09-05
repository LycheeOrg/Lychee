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

use App\DTO\AlbumSortingCriterion;
use App\Enum\AlbumListingScope;
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

	/**
	 * Fixed CodeRabbit finding on PR #4680: TagAlbum/PersonAlbum "matching
	 * albums" results are curated by AlbumPolicy::getUnlockedAlbumIDs()
	 * (session-scoped), so two requests differing only in that state must
	 * never collide on the same cache key - otherwise one visitor's unlock
	 * state could be served to another (guest callers all share a single
	 * `user_id === null` key dimension, making this a real cross-session
	 * disclosure risk, not just a staleness nit).
	 */
	public function testAlbumChildrenDataKeyIsUniqueAcrossUnlockedDigest(): void
	{
		$key_a = $this->provider->albumChildrenDataKey('album1', null, 'digest-a');
		$key_b = $this->provider->albumChildrenDataKey('album1', null, 'digest-b');
		$key_empty = $this->provider->albumChildrenDataKey('album1', null, '');

		self::assertNotSame($key_a, $key_b);
		self::assertNotSame($key_a, $key_empty);
		self::assertNotSame($key_b, $key_empty);
	}

	public function testAlbumChildrenRightsKeyIsUniqueAcrossUnlockedDigest(): void
	{
		$key_a = $this->provider->albumChildrenRightsKey('album1', null, 'digest-a');
		$key_b = $this->provider->albumChildrenRightsKey('album1', null, 'digest-b');
		$key_empty = $this->provider->albumChildrenRightsKey('album1', null, '');

		self::assertNotSame($key_a, $key_b);
		self::assertNotSame($key_a, $key_empty);
		self::assertNotSame($key_b, $key_empty);
	}

	// ── Feature 062 (FR-062-13, NFR-062-08): root/persons/pinned scope ──
	// key uniqueness across (guest-shared, user A own, user A shared,
	// user B own).

	/**
	 * @param callable(AlbumListingScope,int|string|null):string $key_fn
	 */
	private function assertUniqueAcrossScopeAndIdentityMatrix(callable $key_fn): void
	{
		$combinations = [
			'guest-shared' => [AlbumListingScope::SHARED, null],
			'user-a-own' => [AlbumListingScope::OWN, 'user-a'],
			'user-a-shared' => [AlbumListingScope::SHARED, 'user-a'],
			'user-b-own' => [AlbumListingScope::OWN, 'user-b'],
		];

		$keys = [];
		foreach ($combinations as $label => [$scope, $user_id]) {
			$key = $key_fn($scope, $user_id);
			self::assertArrayNotHasKey($key, $keys, "duplicate key generated for {$label}");
			$keys[$key] = $label;
		}

		self::assertCount(4, $keys);
	}

	public function testRootAlbumBucketsKeyIsUniqueAcrossScopeAndIdentityMatrix(): void
	{
		$this->assertUniqueAcrossScopeAndIdentityMatrix(
			fn (AlbumListingScope $scope, int|string|null $user_id) => $this->provider->rootAlbumBucketsKey($scope, $user_id)
		);
	}

	public function testRootAlbumChildrenDataKeyIsUniqueAcrossScopeAndIdentityMatrix(): void
	{
		$this->assertUniqueAcrossScopeAndIdentityMatrix(
			fn (AlbumListingScope $scope, int|string|null $user_id) => $this->provider->rootAlbumChildrenDataKey($scope, $user_id)
		);
	}

	public function testRootAlbumChildrenRightsKeyIsUniqueAcrossScopeAndIdentityMatrix(): void
	{
		$this->assertUniqueAcrossScopeAndIdentityMatrix(
			fn (AlbumListingScope $scope, int|string|null $user_id) => $this->provider->rootAlbumChildrenRightsKey($scope, $user_id)
		);
	}

	public function testPersonAlbumsListingKeyIsUniqueAcrossScopeAndIdentityMatrixAndOmitsSuffixWhenScopeIsNull(): void
	{
		$sorting = AlbumSortingCriterion::createDefault();
		$this->assertUniqueAcrossScopeAndIdentityMatrix(
			fn (AlbumListingScope $scope, int|string|null $user_id) => $this->provider->personAlbumsListingKey($user_id, $sorting, $scope)
		);

		// v2's own Top::get() call (scope omitted) must produce the exact
		// same key it always has — no behavioral change for v2's own cache.
		$without_scope = $this->provider->personAlbumsListingKey(null, $sorting);
		self::assertStringNotContainsString(':scope:', $without_scope);
	}

	public function testPinnedAlbumsListingKeyIsUniqueAcrossScopeAndIdentityMatrixAndOmitsSuffixWhenScopeIsNull(): void
	{
		$this->assertUniqueAcrossScopeAndIdentityMatrix(
			fn (AlbumListingScope $scope, int|string|null $user_id) => $this->provider->pinnedAlbumsListingKey($user_id, null, null, $scope)
		);

		$without_scope = $this->provider->pinnedAlbumsListingKey(null, null, null);
		self::assertStringNotContainsString(':scope:', $without_scope);
	}
}
