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

use App\Enum\StorageDiskType;
use App\Models\Album;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\Feature_v2\Base\BaseApiTest;

class TrackTest extends BaseApiTest
{
	private Album $album;

	protected function setUp(): void
	{
		parent::setUp();
		$owner = User::factory()->create();
		$this->album = Album::factory()->as_root()->create(['owner_id' => $owner->id]);
	}

	public function testUrlAccessorResolvesAgainstLocalDisk(): void
	{
		$track = Track::factory()->for_album($this->album)->create(['file_name' => 'tracks/local.gpx']);

		self::assertSame(StorageDiskType::LOCAL, $track->disk);
		self::assertSame(Storage::disk(StorageDiskType::LOCAL->value)->url('tracks/local.gpx'), $track->url);
	}

	public function testUrlAccessorResolvesAgainstS3Disk(): void
	{
		Storage::fake(StorageDiskType::S3->value);

		$track = Track::factory()->for_album($this->album)->create(['file_name' => 'tracks/remote.gpx', 'disk' => StorageDiskType::S3]);

		self::assertSame(StorageDiskType::S3, $track->disk);
		self::assertSame(Storage::disk(StorageDiskType::S3->value)->url('tracks/remote.gpx'), $track->url);
	}

	public function testDiskDefaultsToLocalOnCreation(): void
	{
		$track = new Track();
		$track->album_id = $this->album->id;
		$track->name = 'unnamed';
		$track->file_name = 'tracks/unnamed.gpx';
		$track->save();

		self::assertSame(StorageDiskType::LOCAL, $track->fresh()->disk);
	}
}
