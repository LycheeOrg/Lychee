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

namespace Tests\Feature_v3\Settings;

use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Jobs\RecomputeAllPhotoBucketsJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * FR-092-13 (S-092-02, S-092-09): instance-wide bucket settings, seen
 * through the v3 photo listing with the managed cache enabled.
 */
class BucketSettingsV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		config(['features.enable-caching' => true]);
		Configs::set('managed_cache_enabled', '1');
		Configs::set('managed_cache_albums_enabled', '1');
	}

	/**
	 * S-092-02: a granularity change shows in an album inheriting the
	 * default, through a warm cache, with no job.
	 */
	public function testGranularityChangeShowsInInheritingAlbumWithoutJob(): void
	{
		$this->requireSe();
		Configs::set('sorting_photos_col', 'taken_at');
		Configs::set('timeline_photos_granularity', 'day');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		foreach (['2026-07-01-09', '2026-07-01-16'] as $key) {
			$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
			DB::table('photos')->where('id', '=', $photo->id)->update(['taken_key' => $key]);
		}
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($before);
		self::assertSame(['2026-07-01'], $before->json('bucket_ids'));

		Queue::fake([RecomputeAllPhotoBucketsJob::class, RecomputeAlbumPhotoBucketsJob::class]);
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'timeline_photos_granularity', 'value' => 'hour']],
		])->assertOk();
		Queue::assertNothingPushed();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($after);
		self::assertSame(['2026-07-01-09', '2026-07-01-16'], $after->json('bucket_ids'));
	}

	/**
	 * S-092-20: an instance photo order change shows in an album inheriting
	 * the default, through a warm cache, with no job.
	 */
	public function testSortOrderChangeFlushesListingsWithoutJob(): void
	{
		Configs::set('sorting_photos_col', 'created_at');
		Configs::set('sorting_photos_order', 'ASC');
		Configs::set('timeline_photos_granularity', 'year');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['created_at' => '2023-05-01 10:00:00']);
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['created_at' => '2024-05-01 10:00:00']);
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($before);
		self::assertSame(['2023', '2024'], $before->json('bucket_ids'));

		Queue::fake([RecomputeAllPhotoBucketsJob::class, RecomputeAlbumPhotoBucketsJob::class]);
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'sorting_photos_order', 'value' => 'DESC']],
		])->assertOk();
		Queue::assertNothingPushed();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($after);
		self::assertSame(['2024', '2023'], $after->json('bucket_ids'));
	}

	/**
	 * S-092-09: a sort column change rewrites every key, then the listing
	 * shows the new buckets through a warm cache.
	 */
	public function testSortColumnChangeRewritesKeysAndFlushesListings(): void
	{
		Configs::set('sorting_photos_col', 'created_at');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_highlighted' => true]);
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();
		$this->assertOk($this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets"));

		config(['queue.default' => 'sync']);
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'sorting_photos_col', 'value' => 'is_highlighted']],
		])->assertOk();

		self::assertSame('1', DB::table('photo_album')->where('album_id', '=', $album->id)->value('bucket_key'));
		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($after);
		self::assertSame(['1'], $after->json('bucket_ids'));
	}
}
