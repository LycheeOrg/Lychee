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

namespace Tests\Feature_v3\Photo;

use App\Enum\RenamerModeType;
use App\Jobs\RecomputePhotoBucketsJob;
use App\Models\Photo;
use App\Models\RenamerRule;
use App\Repositories\ConfigManager;
use App\Services\BucketKeys;
use App\Services\MonthDayKey;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 092 (FR-092-01, FR-092-02, FR-092-10, S-092-04, S-092-13): every
 * write of `taken_at` assigns `photos.taken_key` and every write of `title`
 * assigns `photos.title_date_key`, through the real write path. Feature 106
 * (FR-106-03, S-106-06 … 08): every write of `taken_at` or `created_at`
 * assigns `photos.month_day_key`.
 */
class PhotoKeysWriteTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['queue.default' => 'sync']);
	}

	private function uploadNightImage(string $title): Photo
	{
		return $this->uploadImage(TestConstants::SAMPLE_FILE_NIGHT_IMAGE, $title);
	}

	private function uploadImage(string $file, string $title): Photo
	{
		$response = $this->actingAs($this->admin)->upload('Photo', data: [
			'album_id' => $this->album5->id,
			'file' => static::createUploadedFile($file),
			'file_last_modified_time' => 1678824303000,
			'file_name' => $file,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
			'title' => $title,
		]);
		$this->assertCreated($response);

		return Photo::query()->findOrFail($response->json('expected_id'));
	}

	private function patchPhoto(?string $taken_at, string $title = 'Holiday', string $upload_date = '2021-01-01'): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo', [
			'photo_id' => $this->photo1->id,
			'title' => $title,
			'description' => '',
			'tags' => [],
			'license' => 'none',
			'taken_at' => $taken_at,
			'upload_date' => $upload_date,
			'from_id' => $this->album1->id,
		]);
		$this->assertOk($response);
		$this->photo1->refresh();
	}

	public function testUploadAssignsBothKeys(): void
	{
		$photo = $this->uploadNightImage('2024-03 Trip');

		$this->assertNotNull($photo->taken_at);
		$this->assertSame(BucketKeys::takenKey($photo->taken_at), $photo->taken_key);
		$this->assertSame('2024-03-01', $photo->title_date_key);
	}

	public function testUploadAssignsMonthDayKeyOfTheTakenDate(): void
	{
		$photo = $this->uploadNightImage('Night');

		$this->assertNotNull($photo->taken_at);
		$this->assertSame(MonthDayKey::ofDate($photo->taken_at), $photo->month_day_key);
	}

	public function testUploadWithoutTakenDateAssignsMonthDayKeyOfTheUploadDate(): void
	{
		$this->travelTo(new \DateTimeImmutable('2026-03-07 12:00:00'));
		$previous = resolve(ConfigManager::class)->getValueAsString('use_last_modified_date_when_no_exif_date');
		\Configs::set('use_last_modified_date_when_no_exif_date', false);
		try {
			$photo = $this->uploadImage(TestConstants::SAMPLE_FILE_PNG, 'Drawing');
		} finally {
			\Configs::set('use_last_modified_date_when_no_exif_date', $previous);
		}

		$this->assertNull($photo->taken_at);
		$this->assertSame(307, $photo->month_day_key);
	}

	public function testUpdateAssignsMonthDayKeyOfTheLocalDay(): void
	{
		// 22:30 UTC on 30 June, taken at UTC+02:00 → 1 July where it was taken.
		$this->patchPhoto('2026-07-01T00:30:00+02:00');

		$this->assertSame(701, $this->photo1->month_day_key);
	}

	public function testUpdateResetToInitialTakenAtAssignsMonthDayKey(): void
	{
		DB::table('photos')->where('id', '=', $this->photo1->id)->update(['initial_taken_at' => '2019-05-05 12:00:00', 'initial_taken_at_orig_tz' => 'UTC']);

		$this->patchPhoto(null);

		$this->assertSame(505, $this->photo1->month_day_key);
	}

	public function testUpdateUploadDateWithoutTakenDateAssignsMonthDayKey(): void
	{
		DB::table('photos')->where('id', '=', $this->photo1->id)->update(['taken_at' => null, 'initial_taken_at' => null, 'taken_key' => null]);

		$this->patchPhoto(null, upload_date: '2021-03-07');

		$this->assertNull($this->photo1->taken_at);
		$this->assertSame(307, $this->photo1->month_day_key);
	}

	public function testUpdateAssignsTakenKeyInCaptureZone(): void
	{
		// 14:05 UTC taken at UTC+02:00 → hour 16 in the zone the photo was taken in.
		$this->patchPhoto('2026-07-01T16:05:00+02:00');

		$this->assertSame('2026-07-01-16', $this->photo1->taken_key);
	}

	public function testUpdateResetToInitialTakenAtAssignsTakenKey(): void
	{
		$this->patchPhoto('2026-07-01T16:05:00+02:00');
		$this->patchPhoto(null);

		$this->assertSame(BucketKeys::takenKey($this->photo1->initial_taken_at), $this->photo1->taken_key);
	}

	public function testUpdateAssignsTitleDateKey(): void
	{
		$this->patchPhoto(null, '2024-03-15 Beach');
		$this->assertSame('2024-03-15', $this->photo1->title_date_key);

		$this->patchPhoto(null, '2024-13-45 x');
		$this->assertNull($this->photo1->title_date_key);
	}

	public function testRenameAssignsTitleDateKey(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::rename', [
			'photo_id' => $this->photo1->id,
			'title' => '2023 Summer',
		]);
		$this->assertNoContent($response);

		$this->photo1->refresh();
		$this->assertSame('2023-01-01', $this->photo1->title_date_key);
	}

	public function testBulkRenamerAssignsTitleDateKeyAndRecomputes(): void
	{
		$this->requireSe();
		\Configs::set('renamer_enabled', true);
		Bus::fake([RecomputePhotoBucketsJob::class]);

		RenamerRule::factory()
			->owner_id($this->userMayUpload1->id)
			->order(1)
			->rule('date_rule')
			->description('Date rule')
			->needle('OLD ')
			->replacement('2022-05 ')
			->mode(RenamerModeType::FIRST)
			->state([
				'is_enabled' => true,
				'is_photo_rule' => true,
				'is_album_rule' => false,
			])
			->create();

		$photo = Photo::factory()
			->owned_by($this->userMayUpload1)
			->with_title('OLD Trip')
			->in($this->album1)
			->create();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Renamer', [
			'photo_ids' => [$photo->id],
		]);
		$this->assertNoContent($response);

		$photo->refresh();
		$this->assertSame('2022-05 Trip', $photo->title);
		$this->assertSame('2022-05-01', $photo->title_date_key);
		Bus::assertDispatched(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $photo->id);

		\Configs::set('renamer_enabled', false);
		$this->resetSe();
	}

	public function testTakedateAssignsTakenKeyAndRecomputes(): void
	{
		$photo = $this->uploadNightImage('Night');
		$expected_key = $photo->taken_key;
		$this->assertNotNull($expected_key);
		$expected_month_day_key = MonthDayKey::ofDate($photo->taken_at);

		// Without --force the command only visits photos without `taken_at`:
		// every fixture photo has one, so only this photo is processed.
		DB::table('photos')->where('id', '=', $photo->id)->update([
			'taken_at' => null,
			'taken_key' => null,
			'month_day_key' => null,
		]);

		Bus::fake([RecomputePhotoBucketsJob::class]);
		$this->artisan('lychee:takedate')->assertSuccessful();

		$photo->refresh();
		$this->assertSame($expected_key, $photo->taken_key);
		$this->assertSame($expected_month_day_key, $photo->month_day_key);
		Bus::assertDispatched(RecomputePhotoBucketsJob::class, fn (RecomputePhotoBucketsJob $job) => $job->photo_id === $photo->id);
	}
}
