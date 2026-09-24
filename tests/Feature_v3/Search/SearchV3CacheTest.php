<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Search;

use App\Actions\Search\SearchTokenParser;
use App\Services\Cache\CacheKeyProvider;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I8 — cache-key identity and isolation (S-069-23/24).
 *
 * Asserted at the key-derivation layer rather than by round-tripping through a
 * live cache store: `managed_cache_albums_enabled` requires a configured Redis
 * backend that the test environment does not provide, and the property that
 * actually matters — that two different searches, or two different users, can
 * never collide on one entry — is a property of the key, not of the store.
 */
class SearchV3CacheTest extends BaseApiWithDataTest
{
	private CacheKeyProvider $keys;

	public function setUp(): void
	{
		parent::setUp();
		$this->keys = resolve(CacheKeyProvider::class);
	}

	/**
	 * @param string[] $args
	 */
	private function digest(string $raw, ?string $origin_id = null, ?string $column = null, ?string $order = null): string
	{
		return $this->keys->searchScopeDigest(SearchTokenParser::parse($raw), $origin_id, $column, $order);
	}

	// ── S-069-23 — the same search is the same entry ────────────────

	public function testTheSameSearchProducesTheSameKey(): void
	{
		self::assertSame($this->digest('sunset beach'), $this->digest('sunset beach'));
		self::assertSame(
			$this->keys->searchPhotosKey($this->digest('sunset'), 7, 'd'),
			$this->keys->searchPhotosKey($this->digest('sunset'), 7, 'd'),
		);
	}

	public function testPhotoIdsDigestIsOrderInsensitive(): void
	{
		self::assertSame(
			$this->keys->searchPhotoIdsDigest(['a', 'b', 'c']),
			$this->keys->searchPhotoIdsDigest(['c', 'a', 'b']),
		);
	}

	// ── S-069-24 — isolation ────────────────────────────────────────

	public function testDifferentTermsProduceDifferentKeys(): void
	{
		self::assertNotSame($this->digest('sunset'), $this->digest('sunrise'));
	}

	public function testDifferentModifiersOnTheSameValueProduceDifferentKeys(): void
	{
		self::assertNotSame($this->digest('title:beach'), $this->digest('description:beach'));
	}

	public function testPrefixAndExactMatchProduceDifferentKeys(): void
	{
		self::assertNotSame($this->digest('title:beach*'), $this->digest('title:beach'));
	}

	public function testDifferentOriginAlbumsProduceDifferentKeys(): void
	{
		self::assertNotSame($this->digest('beach', 'album-a'), $this->digest('beach', 'album-b'));
		self::assertNotSame($this->digest('beach'), $this->digest('beach', 'album-a'));
	}

	public function testDifferentSortingProducesDifferentKeys(): void
	{
		self::assertNotSame(
			$this->digest('beach', null, 'title', 'ASC'),
			$this->digest('beach', null, 'title', 'DESC'),
		);
		self::assertNotSame(
			$this->digest('beach', null, 'title', 'ASC'),
			$this->digest('beach', null, 'created_at', 'ASC'),
		);
	}

	public function testDifferentUsersNeverShareAnEntry(): void
	{
		$scope = $this->digest('beach');

		self::assertNotSame(
			$this->keys->searchPhotosKey($scope, 1, 'd'),
			$this->keys->searchPhotosKey($scope, 2, 'd'),
		);
		self::assertNotSame(
			$this->keys->searchPhotosKey($scope, 1, 'd'),
			$this->keys->searchPhotosKey($scope, null, 'd'),
		);
	}

	public function testDifferentUnlockedAlbumStateNeverSharesAnEntry(): void
	{
		$scope = $this->digest('beach');

		self::assertNotSame(
			$this->keys->searchPhotosKey($scope, 1, 'digest-a'),
			$this->keys->searchPhotosKey($scope, 1, 'digest-b'),
		);
	}

	public function testTheFourTiersNeverShareAnEntry(): void
	{
		$scope = $this->digest('beach');
		$keys = [
			$this->keys->searchPhotosKey($scope, 1, 'd'),
			$this->keys->searchPhotoDetailsKey($scope, 'ids', 1, 'd'),
			$this->keys->searchAlbumsKey($scope, 1, 'd'),
			$this->keys->searchAlbumRightsKey($scope, 1, 'd'),
		];

		self::assertSame(count($keys), count(array_unique($keys)));
	}

	public function testDifferentRequestedIdSetsNeverShareADetailsEntry(): void
	{
		$scope = $this->digest('beach');

		self::assertNotSame(
			$this->keys->searchPhotoDetailsKey($scope, $this->keys->searchPhotoIdsDigest(['a']), 1, 'd'),
			$this->keys->searchPhotoDetailsKey($scope, $this->keys->searchPhotoIdsDigest(['b']), 1, 'd'),
		);
	}

	/**
	 * The raw search term must never reach a cache key, since keys surface in
	 * cache-event logs (spec.md Telemetry & Observability).
	 */
	public function testTheRawSearchTermNeverAppearsInAKey(): void
	{
		$term = 'averydistinctivesecretterm';
		$key = $this->keys->searchPhotosKey($this->digest($term), 1, 'd');

		self::assertStringNotContainsString($term, $key);
	}
}
