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
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 103 (FR-103-12, S-103-27): the Timeline hides photos below an NSFW
 * album through the stored `albums.is_recursive_nsfw`, with the same results
 * as the correlated ancestry condition.
 */
class TimelineNsfwFlagTest extends BaseApiWithDataTest
{
	private const KEYS = ['hide_nsfw_in_timeline', 'timeline_photos_order'];

	/** @var array<string,string> */
	private array $initial_values = [];

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		foreach (self::KEYS as $key) {
			$this->initial_values[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}
		Configs::set('hide_nsfw_in_timeline', '1');
		Configs::set('timeline_photos_order', 'created_at');

		$this->assertCreated($this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
			'album_id' => $this->album1->id,
			'is_public' => false,
			'is_link_required' => false,
			'is_nsfw' => true,
			'grants_download' => false,
			'grants_upload' => false,
			'grants_full_photo_access' => false,
		]));
	}

	public function tearDown(): void
	{
		foreach ($this->initial_values as $key => $value) {
			Configs::query()->where('key', '=', $key)->update(['value' => $value]);
		}
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	/**
	 * @return string[]
	 */
	private function timelineIds(): array
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/timeline/Photos');
		$this->assertOk($response);

		return $response->json('ids');
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

	public function testPhotosBelowAnNsfwAlbumAreHidden(): void
	{
		$ids = $this->timelineIds();

		self::assertNotContains($this->photo1->id, $ids);
		self::assertNotContains($this->subPhoto1->id, $ids);
	}

	public function testPhotoWithoutAlbumIsShown(): void
	{
		self::assertContains($this->photoUnsorted->id, $this->timelineIds());
	}

	public function testPhotoAlsoInANonNsfwAlbumIsShownOnce(): void
	{
		$other = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->photo1->albums()->attach($other);

		$ids = $this->timelineIds();

		self::assertSame(1, count(array_keys($ids, $this->photo1->id, true)));
	}

	public function testPhotoQueriesReadTheStoredFlag(): void
	{
		$this->actingAs($this->userMayUpload1);
		$queries = self::queriesOf(function (): void {
			$this->assertOk($this->getJsonV3('Albums/timeline/Photos/buckets'));
			$this->assertOk($this->getJsonV3('Albums/timeline/Photos'));
		});
		$photo_queries = array_values(array_filter($queries, fn (string $sql) => str_contains($sql, 'from photos')));

		self::assertNotEmpty($photo_queries);
		foreach ($photo_queries as $sql) {
			self::assertStringNotContainsString('outers_base_albums.is_nsfw', $sql);
			self::assertStringContainsString('albums.is_recursive_nsfw', $sql);
		}
	}
}
