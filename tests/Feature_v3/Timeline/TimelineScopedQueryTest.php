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

namespace Tests\Feature_v3\Timeline;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 103 (FR-103-09): the Timeline's visible-photo id subquery carries
 * the request's own window or ids, and `buckets` counts without it.
 */
class TimelineScopedQueryTest extends BaseApiWithDataTest
{
	private Photo $twice;

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);

		// One photo in two albums of the same owner.
		$this->twice = Photo::factory()->owned_by($this->userMayUpload1)->in($this->album1)
			->create(['taken_at' => Carbon::create(2001, 2, 3, 12)]);
		$other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->twice->albums()->attach($other);
	}

	/**
	 * @return array{0:string,1:int} the bucket id and count of the 2001-02-03 photo
	 */
	private function bucketOfTwice(): array
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos/buckets');
		$this->assertOk($response);
		foreach ($response->json('bucket_ids') as $i => $bucket_id) {
			if (str_starts_with($bucket_id, '2001')) {
				return [$bucket_id, $response->json('counts')[$i]];
			}
		}
		self::fail('No 2001 bucket.');
	}

	/**
	 * SQL of every query run by `$request`, without identifier quotes.
	 *
	 * @return string[]
	 */
	private static function queriesOf(callable $request): array
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$request();
		$log = DB::getQueryLog();
		DB::disableQueryLog();

		return array_map(fn (array $q) => str_replace(['"', '`'], '', $q['query']), $log);
	}

	/**
	 * The `photos.id in (select …)` subquery of `$sql`, or null.
	 */
	private static function idSubquery(string $sql): ?string
	{
		$start = strpos($sql, 'photos.id in (select');
		if ($start === false) {
			return null;
		}
		$open = $start + strlen('photos.id in ');
		$depth = 0;
		for ($i = $open; $i < strlen($sql); $i++) {
			if ($sql[$i] === '(') {
				$depth++;
			} elseif ($sql[$i] === ')') {
				$depth--;
				if ($depth === 0) {
					return substr($sql, $open + 1, $i - $open - 1);
				}
			}
		}

		return null;
	}

	/**
	 * @return string[] the id subqueries of the queries run by `$request`
	 */
	private static function idSubqueriesOf(callable $request): array
	{
		return array_values(array_filter(array_map(fn (string $sql) => self::idSubquery($sql), self::queriesOf($request))));
	}

	// ── Results (S-103-20) ──────────────────────────────────────────

	public function testPhotoInTwoAlbumsCountsOnce(): void
	{
		[, $count] = $this->bucketOfTwice();
		self::assertSame(1, $count);
	}

	public function testPhotoInTwoAlbumsAppearsOnceInItsWindow(): void
	{
		[$bucket_id] = $this->bucketOfTwice();

		$response = $this->getJsonV3('Albums/timeline/Photos', ['bucket_ids' => [$bucket_id]]);
		$this->assertOk($response);
		self::assertSame([$this->twice->id], $response->json('ids'));
	}

	// ── Query shape (S-103-21) ──────────────────────────────────────

	public function testBucketsCountWithoutIdSubquery(): void
	{
		$this->actingAs($this->userMayUpload1);
		$subqueries = self::idSubqueriesOf(fn () => $this->assertOk($this->getJsonV3('Albums/timeline/Photos/buckets')));

		self::assertSame([], $subqueries);
	}

	public function testWindowRestrictsTheIdSubquery(): void
	{
		[$bucket_id] = $this->bucketOfTwice();

		$subqueries = self::idSubqueriesOf(fn () => $this->assertOk($this->getJsonV3('Albums/timeline/Photos', ['bucket_ids' => [$bucket_id]])));

		self::assertNotEmpty($subqueries);
		foreach ($subqueries as $subquery) {
			self::assertStringContainsString('photos.taken_key >=', $subquery);
		}
	}

	public function testDetailsAndUrlsRestrictTheIdSubquery(): void
	{
		$this->actingAs($this->userMayUpload1);
		$ids = ['photo_ids' => [$this->twice->id]];

		$subqueries = array_merge(
			self::idSubqueriesOf(fn () => $this->assertOk($this->getJsonV3('Albums/timeline/Photos/details', $ids))),
			self::idSubqueriesOf(fn () => $this->assertOk($this->getJsonV3('Albums/timeline/Photos/urls', $ids + ['density' => 1]))),
		);

		self::assertNotEmpty($subqueries);
		foreach ($subqueries as $subquery) {
			self::assertStringContainsString('photos.id in (?', $subquery);
		}
	}
}
