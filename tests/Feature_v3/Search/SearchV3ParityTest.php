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

use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069, I9 — NFR-069-06: for a search whose result fits in one v2 page
 * and contains no multi-album photo, the v3 tiers must reproduce v2's own
 * result, field for field.
 *
 * The "no multi-album photo" precondition is deliberate, not incidental: v2
 * double-counts such a photo (Q-069-08) and v3 fixes that, so including one
 * would make this test assert the bug. The divergence is covered separately and
 * explicitly by `SearchPhotoSourceTest::testPhotoBelongingToThreeAlbumsIsReturnedExactlyOnce`.
 */
class SearchV3ParityTest extends BaseApiWithDataTest
{
	private const TERM = 'CR_';

	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.struct-of-array', true);
		$this->actingAs($this->userMayUpload1);
	}

	/**
	 * @return array{v2: array<string,mixed>, ids: string[]}
	 */
	private function v2(): array
	{
		$response = $this->getJsonWithData('Search', ['album_id' => null, 'terms' => base64_encode(self::TERM)]);
		$this->assertOk($response);
		$body = $response->json();

		return ['v2' => $body, 'ids' => array_column($body['photos'], 'id')];
	}

	/**
	 * @return array<string,mixed>
	 */
	private function v3Photos(): array
	{
		$response = $this->getJsonV3('Search/Photos', ['terms' => base64_encode(self::TERM)]);
		$this->assertOk($response);

		return $response->json();
	}

	/**
	 * @param string[] $ids
	 *
	 * @return array<string,mixed>
	 */
	private function v3Details(array $ids): array
	{
		$response = $this->getJsonV3('Search/Photos/details', [
			'terms' => base64_encode(self::TERM),
			'photo_ids' => $ids,
		]);
		$this->assertOk($response);

		return $response->json();
	}

	public function testBothVersionsMatchTheSamePhotoSet(): void
	{
		$v2 = $this->v2();
		$v3 = $this->v3Photos();

		$v2_ids = $v2['ids'];
		$v3_ids = $v3['ids'];
		sort($v2_ids);
		sort($v3_ids);

		self::assertSame($v2_ids, $v3_ids);
	}

	public function testBothVersionsMatchTheSameAlbumSet(): void
	{
		$v2 = $this->v2();
		$response = $this->getJsonV3('Search/albums', ['terms' => base64_encode(self::TERM)]);
		$this->assertOk($response);

		$v2_ids = array_column($v2['v2']['albums'], 'id');
		$v3_ids = $response->json('ids');
		sort($v2_ids);
		sort($v3_ids);

		self::assertSame($v2_ids, $v3_ids);
	}

	public function testTierTwoReproducesV2sPerPhotoScalars(): void
	{
		$v2 = $this->v2();
		$v3 = $this->v3Photos();

		$v2_by_id = [];
		foreach ($v2['v2']['photos'] as $photo) {
			$v2_by_id[$photo['id']] = $photo;
		}

		self::assertNotCount(0, $v3['ids']);
		foreach ($v3['ids'] as $i => $id) {
			self::assertArrayHasKey($id, $v2_by_id);
			$expected = $v2_by_id[$id];

			self::assertSame($expected['title'], $v3['titles'][$i], "title mismatch for {$id}");
			self::assertSame($expected['type'], $v3['types'][$i], "type mismatch for {$id}");
			self::assertSame($expected['taken_at_orig_tz'], $v3['taken_at_orig_tzs'][$i], "taken_at_orig_tz mismatch for {$id}");
			self::assertSame($expected['precomputed']['is_video'], $v3['is_videos'][$i], "is_video mismatch for {$id}");
			self::assertSame($expected['is_highlighted'], $v3['is_highlighteds'][$i], "is_highlighted mismatch for {$id}");

			// Dates are compared as instants, not as strings: v2 serialises a
			// Carbon instance ("2026-09-22T20:08:16+00:00"), v3 passes the raw
			// DB value straight through ("2026-09-22 20:08:16.988673") because
			// NFR-069-04 forbids constructing a Carbon per row. Same moment,
			// different encoding — the established v3 convention that Features
			// 064/065/066 already ship and that `adaptPhotoTile.ts` consumes.
			self::assertSameInstant($expected['taken_at'], $v3['taken_ats'][$i], "taken_at mismatch for {$id}");
			self::assertSameInstant($expected['created_at'], $v3['created_ats'][$i], "created_at mismatch for {$id}");
		}
	}

	public function testTierThreeReproducesV2sRicherPerPhotoFields(): void
	{
		$v2 = $this->v2();
		$details = $this->v3Details($v2['ids']);

		$v2_by_id = [];
		foreach ($v2['v2']['photos'] as $photo) {
			$v2_by_id[$photo['id']] = $photo;
		}

		self::assertNotCount(0, $details['ids']);
		foreach ($details['ids'] as $i => $id) {
			$expected = $v2_by_id[$id];

			self::assertSame($expected['description'] ?? '', $details['descriptions'][$i] ?? '', "description mismatch for {$id}");
			self::assertSame($expected['checksum'], $details['checksums'][$i], "checksum mismatch for {$id}");
			self::assertSame($expected['original_checksum'], $details['original_checksums'][$i], "original_checksum mismatch for {$id}");
			self::assertSame($expected['license'], $details['licenses'][$i], "license mismatch for {$id}");
			self::assertSame(
				self::withoutOriginalUrl($expected['size_variants']),
				self::withoutOriginalUrl($details['size_variants'][$i]),
				"size_variants mismatch for {$id}"
			);

			$expected_tags = array_column($expected['tags'], 'name');
			$actual_tags = $details['tags'][$i];
			sort($expected_tags);
			sort($actual_tags);
			self::assertSame($expected_tags, $actual_tags, "tags mismatch for {$id}");
		}
	}

	/**
	 * Asserts two datetime strings denote the same instant, regardless of how
	 * each version chose to serialise it.
	 */
	private static function assertSameInstant(?string $expected, ?string $actual, string $message): void
	{
		if ($expected === null || $actual === null) {
			self::assertSame($expected, $actual, $message);

			return;
		}

		self::assertSame(
			(new \DateTimeImmutable($expected))->getTimestamp(),
			(new \DateTimeImmutable($actual))->getTimestamp(),
			$message
		);
	}

	/**
	 * Drops `original.url`, the one field where v2 and v3 legitimately disagree
	 * (Q-069-12).
	 *
	 * v2's search computes `should_downgrade` once per request from the
	 * `grants_full_photo_access` config; the v3 `details` tier inherits Feature
	 * 064's shared projection, which evaluates
	 * `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` per photo. The per-photo check is the
	 * stricter and more correct of the two, and is already shipped behaviour for
	 * Features 064/066 — deliberately not "fixed" here, since that would change
	 * those features rather than this one.
	 *
	 * @param array<string,mixed> $size_variants
	 *
	 * @return array<string,mixed>
	 */
	private static function withoutOriginalUrl(array $size_variants): array
	{
		if (is_array($size_variants['original'] ?? null)) {
			unset($size_variants['original']['url']);
		}

		return $size_variants;
	}

	public function testEveryPhotoIsReturnedExactlyOnceByV3(): void
	{
		$v3 = $this->v3Photos();

		self::assertSame(count($v3['ids']), count(array_unique($v3['ids'])));
	}

	public function testV3ReportsNoTruncationForThisFixture(): void
	{
		$v3 = $this->v3Photos();

		self::assertFalse($v3['is_truncated'], 'the parity fixture must fit under the result cap');
	}
}
