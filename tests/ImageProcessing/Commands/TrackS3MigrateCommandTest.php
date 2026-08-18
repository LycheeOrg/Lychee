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

namespace Tests\ImageProcessing\Commands;

use App\Enum\StorageDiskType;
use App\Jobs\UploadTrackToS3Job;
use App\Models\Track;
use Illuminate\Support\Facades\Bus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Scenarios covered:
 * - S-055-09: `lychee:track_s3_migrate` respects `{limit}`.
 * - S-055-10: command no-ops with an error when `use-s3` is inactive.
 */
class TrackS3MigrateCommandTest extends BaseApiWithDataTest
{
	public const COMMAND = 'lychee:track_s3_migrate';

	public function testNoOpsWithErrorWhenUseS3Inactive(): void
	{
		config(['features.use-s3' => false]);

		$this->artisan(self::COMMAND, [])
			->expectsOutputToContain('S3 support is not activated.')
			->assertSuccessful();
	}

	public function testRespectsLimitAndDispatchesOneJobPerMigratedTrack(): void
	{
		config(['features.use-s3' => true]);
		Bus::fake();

		Track::factory()->for_album($this->album1)->count(8)->create(['disk' => StorageDiskType::LOCAL]);

		$this->artisan(self::COMMAND, ['limit' => 5])->assertSuccessful();

		Bus::assertDispatchedTimes(UploadTrackToS3Job::class, 5);
	}

	public function testNoOpsQuietlyWhenNoLocalTracksExist(): void
	{
		config(['features.use-s3' => true]);
		Bus::fake();

		$this->artisan(self::COMMAND, [])
			->expectsOutputToContain('No files require migrations.')
			->assertSuccessful();

		Bus::assertNotDispatched(UploadTrackToS3Job::class);
	}
}
