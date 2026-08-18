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

use App\Actions\Album\Delete;
use App\Enum\StorageDiskType;
use App\Jobs\FileDeleterJob;
use App\Models\Album;
use App\Models\Track;
use Illuminate\Support\Facades\Bus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * FR-055-12: album deletion collects tracks recursively, grouped by disk,
 * and dispatches one {@link FileDeleterJob} per distinct disk (closing the
 * pre-existing hardcoded-`LOCAL` gap in `Actions\Album\Delete`).
 *
 * Scenarios covered:
 * - S-055-11: one local + one S3 track dispatch two correctly-paired jobs.
 * - S-055-12: generalises to a subtree (parent + descendant sub-album).
 */
class AlbumDeleteTrackTest extends BaseApiWithDataTest
{
	public function testDeleteDispatchesOneFileDeleterJobPerDiskAcrossSubtree(): void
	{
		Bus::fake();

		$localTrack = Track::factory()->for_album($this->subAlbum1)->create([
			'disk' => StorageDiskType::LOCAL,
			'file_name' => 'tracks/local1.gpx',
		]);
		$s3Track = Track::factory()->for_album($this->album1)->create([
			'disk' => StorageDiskType::S3,
			'file_name' => 'tracks/s3-1.gpx',
		]);
		$s3TrackDescendant = Track::factory()->for_album($this->subAlbum1)->create([
			'disk' => StorageDiskType::S3,
			'file_name' => 'tracks/s3-2.gpx',
		]);

		(new Delete())->do([$this->album1->id]);

		Bus::assertDispatched(
			FileDeleterJob::class,
			fn (FileDeleterJob $job) => $job->storage_type === StorageDiskType::LOCAL &&
				$job->file_list === [$localTrack->file_name]
		);

		Bus::assertDispatched(
			FileDeleterJob::class,
			fn (FileDeleterJob $job) => $job->storage_type === StorageDiskType::S3 &&
				count($job->file_list) === 2 &&
				in_array($s3Track->file_name, $job->file_list, true) &&
				in_array($s3TrackDescendant->file_name, $job->file_list, true)
		);

		self::assertSame(
			0,
			Track::query()->whereIn('id', [$localTrack->id, $s3Track->id, $s3TrackDescendant->id])->count()
		);
	}

	public function testDeleteWithNoTracksDispatchesNoFileDeleterJob(): void
	{
		Bus::fake();

		// A fresh, photo-less sub-album isolates the track-cleanup dispatch
		// from the unrelated FileDeleterJob dispatched for photo files.
		$emptyAlbum = Album::factory()->children_of($this->album1)->owned_by($this->userMayUpload1)->create();

		(new Delete())->do([$emptyAlbum->id]);

		Bus::assertNotDispatched(FileDeleterJob::class);
	}
}
