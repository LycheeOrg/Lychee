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

use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Jobs\RecomputeAlbumPhotoBucketsJob;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Jobs\RecomputeAllAlbumBucketsJob;
use App\Jobs\RecomputeAllPhotoBucketsJob;
use App\Jobs\RecomputeChildAlbumBucketsJob;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * FR-092-13 (S-092-02, S-092-09, S-092-20, S-092-22): instance-wide bucket settings, seen
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
	 * S-092-22: a photo label format change shows through a warm cache,
	 * with no job.
	 */
	public function testPhotoLabelFormatChangeFlushesListingsWithoutJob(): void
	{
		$this->requireSe();
		Configs::set('sorting_photos_col', 'created_at');
		Configs::set('timeline_photos_granularity', 'year');
		Configs::set('timeline_photo_date_format_year', 'Y');
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['created_at' => '2024-05-01 10:00:00']);
		(new RecomputeAlbumPhotoBucketsJob($album->id))->handle();

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($before);
		self::assertSame(['2024'], $before->json('labels'));

		Queue::fake([RecomputeAllPhotoBucketsJob::class, RecomputeAlbumPhotoBucketsJob::class]);
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'timeline_photo_date_format_year', 'value' => 'y']],
		])->assertOk();
		Queue::assertNothingPushed();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$album->id}/Photos/buckets");
		$this->assertOk($after);
		self::assertSame(['24'], $after->json('labels'));
	}

	/**
	 * S-092-22: an album label format change shows through a warm cache,
	 * with no job.
	 */
	public function testAlbumLabelFormatChangeFlushesListingsWithoutJob(): void
	{
		$this->requireSe();
		Configs::set('timeline_albums_granularity', 'year');
		Configs::set('timeline_album_date_format_year', 'Y');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();
		$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon('2024-05-01')]);
		(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($before);
		self::assertSame(['2024'], $before->json('labels'));

		Queue::fake([RecomputeAllAlbumBucketsJob::class, RecomputeChildAlbumBucketsJob::class]);
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'timeline_album_date_format_year', 'value' => 'y']],
		])->assertOk();
		Queue::assertNothingPushed();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($after);
		self::assertSame(['24'], $after->json('labels'));
	}

	/**
	 * S-092-22: an album granularity change shows in the children of a
	 * parent inheriting the default, through a warm cache, with no job.
	 */
	public function testAlbumGranularityChangeShowsInChildListingWithoutJob(): void
	{
		$this->requireSe();
		Configs::set('timeline_albums_granularity', 'year');
		$parent = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$parent->album_sorting = new AlbumSortingCriterion(ColumnSortingType::CREATED_AT, OrderSortingType::ASC);
		$parent->save();
		foreach (['2024-05-01', '2024-07-01'] as $date) {
			$child = Album::factory()->children_of($parent)->owned_by($this->userMayUpload1)->create(['created_at' => new Carbon($date)]);
			(new RecomputeAlbumStatsJob($child->id, propagate_to_parent: false))->handle();
		}

		$before = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($before);
		self::assertSame(['2024'], $before->json('bucket_ids'));

		Queue::fake([RecomputeAllAlbumBucketsJob::class, RecomputeChildAlbumBucketsJob::class]);
		$this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'timeline_albums_granularity', 'value' => 'month']],
		])->assertOk();
		Queue::assertNothingPushed();

		$after = $this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$parent->id}/buckets");
		$this->assertOk($after);
		self::assertSame(['2024-05', '2024-07'], $after->json('bucket_ids'));
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
