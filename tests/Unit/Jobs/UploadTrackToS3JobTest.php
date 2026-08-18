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

namespace Tests\Unit\Jobs;

use App\Enum\JobStatus;
use App\Enum\StorageDiskType;
use App\Jobs\UploadTrackToS3Job;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

class UploadTrackToS3JobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testUploadMovesFileToS3AndUpdatesDisk(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		Storage::fake(StorageDiskType::S3->value);

		$user = User::factory()->create();
		$album = Album::factory()->as_root()->create(['owner_id' => $user->id]);
		$track = Track::factory()->for_album($album)->create(['file_name' => 'tracks/foo.gpx']);
		Storage::disk(StorageDiskType::LOCAL->value)->put($track->file_name, 'gpx-data');

		(new UploadTrackToS3Job($track, $user->id))->handle();

		Storage::disk(StorageDiskType::S3->value)->assertExists($track->file_name);
		Storage::disk(StorageDiskType::LOCAL->value)->assertMissing($track->file_name);

		$track->refresh();
		self::assertEquals(StorageDiskType::S3, $track->disk);

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::SUCCESS, $history->status);
	}

	public function testFailedMarksHistoryAndLogs(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->create(['owner_id' => $user->id]);
		$track = Track::factory()->for_album($album)->create();

		Log::shouldReceive('channel')->twice()->with('jobs')->andReturnSelf();
		Log::shouldReceive('error')->twice();

		$job = new UploadTrackToS3Job($track, $user->id);
		$job->failed(new \Exception('boom'));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}

	public function testFailedReleasesJobWhenExceptionCodeIs999(): void
	{
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->create(['owner_id' => $user->id]);
		$track = Track::factory()->for_album($album)->create();

		Log::shouldReceive('channel')->never();

		$job = new UploadTrackToS3Job($track, $user->id);
		$job->failed(new \Exception('retry me', 999));

		$history = JobHistory::query()->where('owner_id', '=', $user->id)->latest()->first();
		self::assertEquals(JobStatus::FAILURE, $history->status);
	}
}
