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

use App\Enum\SizeVariantType;
use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatePhoto;
use Tests\Traits\RequiresFFMpeg;

class VideoDataTest extends BaseApiWithDataTest
{
	use RequiresFFMpeg;
	use CreatePhoto;

	public const COMMAND = 'lychee:video_data';

	public function testThumbRecreation(): void
	{
		$this->assertHasFFMpegOrSkip();

		$photo1 = $this->createPhoto(TestConstants::SAMPLE_FILE_TRAIN_VIDEO);

		// Remove the size variant "thumb" from disk and from DB
		\Safe\unlink(public_path($this->dropUrlPrefix($photo1['size_variants']['thumb']['url'])));
		DB::table('size_variants')
			->where('photo_id', '=', $photo1['id'])
			->where('type', '=', SizeVariantType::THUMB)
			->delete();

		// Re-create it
		$this->artisan(self::COMMAND)
			->assertExitCode(0);

		// Get updated video and check if thumb has been re-created
		$this->clearCachedSmartAlbums();
		$response = $this->getJsonWithData('Album', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$photo2 = $response->json('resource.photos.0');
		self::assertNotNull($photo2['size_variants']['thumb']);
		self::assertEquals($photo1['size_variants']['thumb']['width'], $photo2['size_variants']['thumb']['width']);
		self::assertEquals($photo1['size_variants']['thumb']['height'], $photo2['size_variants']['thumb']['height']);
		self::assertFileExists(public_path($this->dropUrlPrefix($photo2['size_variants']['thumb']['url'])));
	}
}