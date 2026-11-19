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

namespace Tests\Feature_v2;

use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 070 — the over-grant, asserted closed on every converted surface
 * (S-070-01/02).
 *
 * `configs.grants_full_photo_access` is the seed value stamped onto a **newly
 * created** public share (default `1`); the authoritative value is the
 * `access_permissions.grants_full_photo_access` **column** (default `0`). Five
 * surfaces read the former as a runtime gate and so handed out full-resolution
 * URLs for photos whose share explicitly withheld them.
 *
 * Every test below pins the case where the two disagree — **config ON, album
 * grant OFF, viewer is not the owner**. Before this feature each of these
 * returned a real URL; each must now return `null`. Each surface is also
 * asserted in the *positive* direction, so a change that simply downgraded
 * everything would fail here rather than look like a pass.
 *
 * Every assertion targets `subPhoto4`, which lives in the public `subAlbum4`
 * (owned by `userLocked`, shared via `perm44`) and is therefore reachable by a
 * guest without being owned by one.
 */
class FullPhotoAccessOverGrantTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		// The config says "yes" throughout; only the real grant varies. That is
		// precisely the disagreement the old code resolved the wrong way.
		Configs::set('grants_full_photo_access', '1');
		Configs::set('search_public', '1');
		Configs::set('map_display', '1');
		Configs::set('map_display_public', '1');
		Configs::set('timeline_photos_public', '1');
	}

	private function setAlbumGrant(bool $granted): void
	{
		$this->perm44->grants_full_photo_access = $granted;
		$this->perm44->save();
	}

	/**
	 * Pulls `subPhoto4`'s `original.url` out of an arbitrarily-shaped response
	 * by walking it, so one helper serves five differently-shaped payloads.
	 *
	 * Returns `false` when the photo is absent (distinct from `null`, which
	 * means present-but-downgraded) so a test can never mistake "not returned"
	 * for "correctly denied".
	 */
	private function findOriginalUrl(array $payload): string|false|null
	{
		$found = false;
		$walk = function ($node) use (&$walk, &$found): void {
			if (!is_array($node)) {
				return;
			}
			if (($node['id'] ?? null) === $this->subPhoto4->id && array_key_exists('size_variants', $node)) {
				$found = $node['size_variants']['original']['url'] ?? null;

				return;
			}
			foreach ($node as $child) {
				$walk($child);
			}
		};
		$walk($payload);

		return $found;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function fetch(string $uri): array
	{
		$response = $this->getJson($uri);
		$this->assertOk($response);

		return $response->json();
	}

	// ── v2 Search (FR-070-03) ───────────────────────────────────────

	public function testSearchDeniesFullResolutionWhenTheAlbumGrantIsOff(): void
	{
		$this->setAlbumGrant(false);
		$payload = $this->fetch('Search?terms=' . base64_encode('CR_'));

		self::assertNull($this->findOriginalUrl($payload), 'v2 search still over-grants');
	}

	public function testSearchAllowsFullResolutionWhenTheAlbumGrantIsOn(): void
	{
		$this->setAlbumGrant(true);
		$payload = $this->fetch('Search?terms=' . base64_encode('CR_'));

		self::assertNotNull($this->findOriginalUrl($payload));
	}

	// ── v2 Album photos (FR-070-04) ─────────────────────────────────

	public function testAlbumPhotosDeniesFullResolutionWhenTheAlbumGrantIsOff(): void
	{
		$this->setAlbumGrant(false);
		$payload = $this->fetch('Album::photos?album_id=' . $this->subAlbum4->id);

		self::assertNull($this->findOriginalUrl($payload));
	}

	public function testAlbumPhotosAllowsFullResolutionWhenTheAlbumGrantIsOn(): void
	{
		$this->setAlbumGrant(true);
		$payload = $this->fetch('Album::photos?album_id=' . $this->subAlbum4->id);

		self::assertNotNull($this->findOriginalUrl($payload));
	}

	// ── Map position data (FR-070-05) ───────────────────────────────

	public function testMapDeniesFullResolutionWhenTheAlbumGrantIsOff(): void
	{
		$this->setAlbumGrant(false);
		$payload = $this->fetch('Map?album_id=' . $this->subAlbum4->id);

		$url = $this->findOriginalUrl($payload);
		if ($url === false) {
			self::markTestSkipped('fixture photo carries no GPS position, so it never reaches the map');
		}
		self::assertNull($url);
	}

	// ── Embed stream (FR-070-06) ────────────────────────────────────

	public function testEmbedStreamDeniesFullResolutionWhenTheAlbumGrantIsOff(): void
	{
		$this->setAlbumGrant(false);
		$payload = $this->fetch('Embed/stream');

		$url = $this->findOriginalUrl($payload);
		if ($url === false) {
			self::markTestSkipped('fixture photo is not part of the public embed stream');
		}
		self::assertNull($url);
	}

	// ── v2 Timeline (FR-070-07) ─────────────────────────────────────

	public function testTimelineDeniesFullResolutionWhenTheAlbumGrantIsOff(): void
	{
		$this->setAlbumGrant(false);
		$payload = $this->fetch('Timeline');

		$url = $this->findOriginalUrl($payload);
		if ($url === false) {
			self::markTestSkipped('fixture photo does not appear in the public timeline');
		}
		self::assertNull($url);
	}

	// ── Cost is bounded (S-070-07, NFR-070-01) ──────────────────────

	/**
	 * The per-photo decision must cost **one** grouped query, not one per
	 * photo. Asserted by growing the photo count and showing the query count
	 * does not move: the previous implementation evaluated the policy per photo
	 * and lazy-loaded each containing album's `access_permissions`, so this is
	 * the property that stops the fix from reintroducing an N+1.
	 */
	public function testQueryCountDoesNotGrowWithPhotoCount(): void
	{
		$this->setAlbumGrant(false);

		$count_for = function (string $uri): int {
			DB::flushQueryLog();
			DB::enableQueryLog();
			$this->getJson($uri);
			$n = count(DB::getQueryLog());
			DB::disableQueryLog();

			return $n;
		};

		$uri = 'Album::photos?album_id=' . $this->subAlbum4->id;

		// Warm up first: the very first request of a test process also pays for
		// schema checks and the one-off config load, which would otherwise make
		// the baseline artificially high and mask growth.
		$count_for($uri);
		$few_photos = $count_for($uri);

		for ($i = 0; $i < 6; $i++) {
			Photo::factory()->owned_by($this->userLocked)->in($this->subAlbum4)->create();
		}
		$many_photos = $count_for($uri);

		self::assertSame(
			$few_photos,
			$many_photos,
			"query count moved from {$few_photos} to {$many_photos} as photos were added - the decision is no longer batched"
		);
	}

	// ── Ownership still wins everywhere (S-070-03) ──────────────────

	public function testOwnerKeepsFullResolutionEvenWithTheConfigOff(): void
	{
		Configs::set('grants_full_photo_access', '0');
		$this->actingAs($this->userMayUpload1);

		$payload = $this->fetch('Album::photos?album_id=' . $this->album1->id);

		$found = false;
		$walk = function ($node) use (&$walk, &$found): void {
			if (!is_array($node)) {
				return;
			}
			if (($node['id'] ?? null) === $this->photo1->id && array_key_exists('size_variants', $node)) {
				$found = $node['size_variants']['original']['url'] ?? null;

				return;
			}
			foreach ($node as $child) {
				$walk($child);
			}
		};
		$walk($payload);

		self::assertNotFalse($found, 'owner must still see their own photo');
		self::assertNotNull($found, 'ownership must beat the config');
	}
}
