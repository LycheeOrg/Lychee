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

namespace Tests\Unit\Jobs;

use App\Enum\JobStatus;
use App\Enum\SizeVariantType;
use App\Jobs\EmbedMetadataJob;
use App\Models\Configs;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\PhotoRating;
use App\Models\SizeVariant;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

/**
 * Covers Feature 059's App\Jobs\EmbedMetadataJob end-to-end (against real,
 * fake-disk-backed files so the post-write checksum/filesize re-measure is
 * exercised for real), faking only the `exiftool` invocation itself via
 * Process::fake() (NFR-059-04 — no real exiftool binary required).
 */
class EmbedMetadataJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	protected function setUp(): void
	{
		parent::setUp();
		Storage::fake('images');
		Configs::set('embed_metadata_in_files_enabled', true);
		Configs::set('embed_metadata_update_checksum_enabled', true);
		Configs::set('has_exiftool', 1);
		Configs::set('exiftool_path', '/usr/bin/exiftool');
	}

	protected function tearDown(): void
	{
		// Config changes are not part of the DatabaseTransactions rollback
		// for this suite's shared SQLite file — restore the defaults
		// explicitly so a later test file in a full-suite run isn't left
		// with the feature unexpectedly enabled.
		Configs::set('embed_metadata_in_files_enabled', false);
		Configs::set('embed_metadata_update_checksum_enabled', true);
		Configs::set('has_exiftool', 0);
		Configs::set('exiftool_path', '');
		parent::tearDown();
	}

	/**
	 * `Photo::factory()` always creates its standard 7 size variants
	 * (`without_size_variants()` is pre-existing, unused-elsewhere factory
	 * state that doesn't actually take effect — its `afterCreating` hook
	 * stays bound to the original, un-mutated factory instance). Rather
	 * than fix that unrelated factory quirk here, we just use the
	 * auto-created Original variant and give it real bytes on the fake disk.
	 */
	private function makePhotoWithLocalOriginal(User $user): Photo
	{
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = $photo->size_variants->getOriginal();
		Storage::disk('images')->put($variant->short_path, 'original-bytes');

		return $photo->refresh();
	}

	public function testUniqueIdIncludesPhotoId(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->without_size_variants()->create();

		$job = new EmbedMetadataJob($photo);

		self::assertEquals('embed-metadata:' . $photo->id, $job->uniqueId());
	}

	public function testDisabledConfigIsANoOpSuccess(): void
	{
		Configs::set('embed_metadata_in_files_enabled', false);
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);

		(new EmbedMetadataJob($photo))->handle();

		Process::assertNothingRan();
		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testMissingExiftoolFails(): void
	{
		Configs::set('has_exiftool', 0);
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);

		(new EmbedMetadataJob($photo))->handle();

		Process::assertNothingRan();
		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testSuccessfulEmbedUpdatesChecksumAndFilesize(): void
	{
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);
		$photo->title = 'New Title';
		$photo->description = 'New Description';
		$photo->save();
		$tag = Tag::create(['name' => 'sunset']);
		$photo->tags()->sync([$tag->id]);
		PhotoRating::create(['photo_id' => $photo->id, 'user_id' => $user->id, 'rating' => 4]);

		$original_checksum = $photo->checksum;
		$original_filesize = $photo->size_variants->getOriginal()->filesize;

		(new EmbedMetadataJob($photo))->handle();

		Process::assertRan(function (PendingProcess $process) {
			self::assertContains('-XMP-dc:Title=New Title', $process->command);
			self::assertContains('-IPTC:Keywords+=sunset', $process->command);
			self::assertContains('-EXIF:Rating=4', $process->command);

			return true;
		});

		$photo->refresh();
		$variant = $photo->size_variants->getOriginal();
		self::assertNotEquals($original_checksum, $photo->checksum);
		self::assertEquals($photo->checksum, $photo->original_checksum);
		self::assertNotEquals($original_filesize, $variant->filesize);
		self::assertEquals(strlen('original-bytes'), $variant->filesize);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testChecksumUpdateDisabledStillRefreshesFilesizeOnly(): void
	{
		Configs::set('embed_metadata_update_checksum_enabled', false);
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);
		$original_checksum = $photo->checksum;

		(new EmbedMetadataJob($photo))->handle();

		$photo->refresh();
		self::assertEquals($original_checksum, $photo->checksum);
		self::assertEquals(strlen('original-bytes'), $photo->size_variants->getOriginal()->filesize);
	}

	public function testOwnerHasNoRatingProducesNullRatingPayload(): void
	{
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);

		(new EmbedMetadataJob($photo))->handle();

		Process::assertRan(function (PendingProcess $process) {
			self::assertContains('-EXIF:Rating=', $process->command);
			self::assertNotContains('-EXIF:Rating=0', $process->command);

			return true;
		});
	}

	public function testBothOriginalAndRawAreEmbeddedWhenPresent(): void
	{
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);
		$raw = SizeVariant::factory()->for_photo($photo)->type(SizeVariantType::RAW)->create();
		Storage::disk('images')->put($raw->short_path, 'raw-bytes');

		(new EmbedMetadataJob($photo))->handle();

		Process::assertRanTimes(fn (PendingProcess $process) => true, 2);
		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testRawFailureDoesNotAbortOriginalWrite(): void
	{
		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);
		$raw = SizeVariant::factory()->for_photo($photo)->type(SizeVariantType::RAW)->create();
		Storage::disk('images')->put($raw->short_path, 'raw-bytes');
		$raw_real_path = Storage::disk('images')->path($raw->short_path);

		Process::fake(function (PendingProcess $process) use ($raw_real_path) {
			$target = end($process->command);

			return $target === $raw_real_path ? Process::result(exitCode: 1) : Process::result(exitCode: 0);
		});

		(new EmbedMetadataJob($photo))->handle();

		$photo->refresh();
		self::assertNotNull($photo->checksum);
		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testOriginalFailureDoesNotAbortRawWriteSymmetric(): void
	{
		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);
		$raw = SizeVariant::factory()->for_photo($photo)->type(SizeVariantType::RAW)->create();
		Storage::disk('images')->put($raw->short_path, 'raw-bytes');
		$original_real_path = Storage::disk('images')->path($photo->size_variants->getOriginal()->short_path);

		Process::fake(function (PendingProcess $process) use ($original_real_path) {
			$target = end($process->command);

			return $target === $original_real_path ? Process::result(exitCode: 1) : Process::result(exitCode: 0);
		});

		(new EmbedMetadataJob($photo))->handle();

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testBothVariantsFailingReportsFailure(): void
	{
		Process::fake(fn () => Process::result(exitCode: 1));

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);

		(new EmbedMetadataJob($photo))->handle();

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testNonLocalDiskIsSkippedNotFailed(): void
	{
		Process::fake();

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$photo->size_variants->getOriginal()->update(['storage_disk' => 's3']);
		$photo->refresh();

		(new EmbedMetadataJob($photo))->handle();

		Process::assertNothingRan();
		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	/**
	 * NFR-059-07/S-059-19: an unexpected exception at a stage outside the
	 * per-variant Writer::embed() calls (here: the photo no longer exists
	 * by the time handle() runs, so the mandatory refresh() throws) must
	 * still be caught and never escape handle().
	 */
	public function testUnexpectedExceptionOutsidePerVariantWriteNeverEscapesHandle(): void
	{
		Process::fake();

		$user = User::factory()->create();
		$photo = $this->makePhotoWithLocalOriginal($user);
		$job = new EmbedMetadataJob($photo);

		Photo::query()->where('id', $photo->id)->delete();

		// Must not throw.
		$job->handle();

		Process::assertNothingRan();
		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}
}
