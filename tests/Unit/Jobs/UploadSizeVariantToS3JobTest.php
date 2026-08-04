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
use App\Enum\StorageDiskType;
use App\Jobs\UploadSizeVariantToS3Job;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

class UploadSizeVariantToS3JobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testUploadsOriginalVariantAndItsLivePhotoPartner(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::fake(StorageDiskType::S3->value);

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create(['live_photo_short_path' => 'live/foo.mov']);
		Storage::disk(StorageDiskType::LOCAL->value)->put('live/foo.mov', 'live-data');

		$variant = SizeVariant::where('photo_id', $photo->id)->where('type', '=', SizeVariantType::ORIGINAL)->first();
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'variant-data');

		(new UploadSizeVariantToS3Job($variant, $user->id))->handle();

		Storage::disk(StorageDiskType::S3->value)->assertExists($variant->short_path);
		Storage::disk(StorageDiskType::LOCAL->value)->assertMissing($variant->short_path);
		Storage::disk(StorageDiskType::S3->value)->assertExists('live/foo.mov');
		Storage::disk(StorageDiskType::LOCAL->value)->assertMissing('live/foo.mov');

		$variant->refresh();
		self::assertEquals(StorageDiskType::S3, $variant->storage_disk);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testUploadsNonOriginalVariantWithoutTouchingLivePhoto(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::fake(StorageDiskType::S3->value);

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create(['live_photo_short_path' => 'live/foo.mov']);

		$variant = SizeVariant::where('photo_id', $photo->id)->where('type', '=', SizeVariantType::THUMB)->first();
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'variant-data');

		(new UploadSizeVariantToS3Job($variant, $user->id))->handle();

		Storage::disk(StorageDiskType::S3->value)->assertExists($variant->short_path);
		Storage::disk(StorageDiskType::S3->value)->assertMissing('live/foo.mov');
	}

	public function testSkipsWatermarkedPathWhenAbsent(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::fake(StorageDiskType::S3->value);

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();

		$variant = SizeVariant::where('photo_id', $photo->id)->where('type', '=', SizeVariantType::THUMB)->first();
		self::assertNull($variant->short_path_watermarked);
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'variant-data');

		(new UploadSizeVariantToS3Job($variant, $user->id))->handle();

		Storage::disk(StorageDiskType::S3->value)->assertExists($variant->short_path);
	}

	public function testUploadsWatermarkedPathWhenPresent(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::fake(StorageDiskType::S3->value);

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();

		$variant = SizeVariant::where('photo_id', $photo->id)->where('type', '=', SizeVariantType::THUMB)->first();
		$variant->short_path_watermarked = 'watermarked/' . $variant->short_path;
		$variant->save();
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'variant-data');
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path_watermarked, 'watermarked-data');

		(new UploadSizeVariantToS3Job($variant, $user->id))->handle();

		Storage::disk(StorageDiskType::S3->value)->assertExists($variant->short_path_watermarked);
		Storage::disk(StorageDiskType::LOCAL->value)->assertMissing($variant->short_path_watermarked);
	}

	public function testSkipsLivePhotoPartnerWhenAbsent(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::fake(StorageDiskType::S3->value);

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create(['live_photo_short_path' => null]);

		$variant = SizeVariant::where('photo_id', $photo->id)->where('type', '=', SizeVariantType::ORIGINAL)->first();
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'variant-data');

		(new UploadSizeVariantToS3Job($variant, $user->id))->handle();

		Storage::disk(StorageDiskType::S3->value)->assertExists($variant->short_path);
	}

	public function testFailedMarksHistoryAndLogs(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		Log::shouldReceive('channel')->twice()->with('jobs')->andReturnSelf();
		Log::shouldReceive('error')->twice();

		$job = new UploadSizeVariantToS3Job($variant, $user->id);
		$job->failed(new \Exception('boom'));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testFailedReleasesJobWhenExceptionCodeIs999(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		Log::shouldReceive('channel')->never();

		$job = new UploadSizeVariantToS3Job($variant, $user->id);
		// No underlying queue `$job` is bound, so `release()` is a safe no-op.
		$job->failed(new \Exception('retry me', 999));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}
}
