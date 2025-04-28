<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v2\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiV2Test;
use Tests\Traits\CreatePhoto;

class TakeDateTest extends BaseApiV2Test
{
	use CreatePhoto;

	public const COMMAND = 'lychee:takedate';

	public function testNoUpdateRequired(): void
	{
		$this->artisan(self::COMMAND)
			->expectsOutput('No pictures require takedate updates.')
			->assertExitCode(-1);
	}

	public function testSetUploadTimeFromFileTime(): void
	{
		// Make sure that the tables are empty before running this tests.
		DB::table('size_variants')->delete();
		DB::table('photos')->delete();

		$photo1 = $this->createPhoto(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE);

		DB::table('photos')
			->where('id', '=', $photo1['id'])
			->update(['created_at' => Carbon::createFromDate(1970, 01, 01)->format('Y-m-d H:i:s.u')]);

		$this->artisan(self::COMMAND, [
			'--set-upload-time' => true,
			'--force' => true,
		])
			->assertSuccessful();

		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$photo2 = $response->json('resource.photos.0');

		$file_time = \Safe\filemtime(public_path($this->dropUrlPrefix($photo2['size_variants']['original']['url'])));
		$carbon = new Carbon($photo2['created_at']);

		self::assertEquals($file_time, $carbon->getTimestamp());
	}
}