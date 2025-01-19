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

namespace Tests\Feature_v1;

use App\Enum\SizeVariantType;
use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;

class CommandVideoDataTest extends BasePhotoTest
{
	public const COMMAND = 'lychee:video_data';

	public function testThumbRecreation(): void
	{
		$this->assertHasFFMpegOrSkip();

		/** @var \App\Models\Photo $photo1 */
		$photo1 = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestConstants::SAMPLE_FILE_TRAIN_VIDEO)
		));

		// Remove the size variant "thumb" from disk and from DB
		\Safe\unlink(public_path($this->dropUrlPrefix($photo1->size_variants->thumb->url)));
		DB::table('size_variants')
			->where('photo_id', '=', $photo1->id)
			->where('type', '=', SizeVariantType::THUMB)
			->delete();

		// Re-create it
		$this->artisan(self::COMMAND)
			->assertExitCode(0);

		// Get updated video and check if thumb has been re-created
		/** @var \App\Models\Photo $photo2 */
		$photo2 = static::convertJsonToObject($this->photos_tests->get($photo1->id));
		self::assertNotNull($photo2->size_variants->thumb);
		self::assertEquals($photo1->size_variants->thumb->width, $photo2->size_variants->thumb->width);
		self::assertEquals($photo1->size_variants->thumb->height, $photo2->size_variants->thumb->height);
		self::assertFileExists(public_path($this->dropUrlPrefix($photo2->size_variants->thumb->url)));
	}
}
