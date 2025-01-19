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
use function Safe\unlink;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;

class CommandGenerateThumbsTest extends BasePhotoTest
{
	public const COMMAND = 'lychee:generate_thumbs';

	public function testNoArguments(): void
	{
		$this->expectExceptionMessage('Not enough arguments (missing: "type").');
		$this->artisan(self::COMMAND)
			->run();
	}

	public function testInvalidSizeVariantArgument(): void
	{
		$this->artisan(self::COMMAND, ['type' => 'smally'])
			->expectsOutput('Type smally is not one of placeholder, thumb, thumb2x, small, small2x, medium, medium2x')
			->assertExitCode(1);
	}

	public function testNoSizeVariantsMissing(): void
	{
		$this->artisan(self::COMMAND, ['type' => 'small'])
			->expectsOutput('No picture requires small.')
			->assertExitCode(0);
	}

	public function testThumbRecreation(): void
	{
		/** @var \App\Models\Photo $photo1 */
		$photo1 = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		));

		// Remove the size variant "small" from disk and from DB
		unlink(public_path($this->dropUrlPrefix($photo1->size_variants->small->url)));
		DB::table('size_variants')
			->where('photo_id', '=', $photo1->id)
			->where('type', '=', SizeVariantType::SMALL)
			->delete();

		// Re-create it
		$this->artisan(self::COMMAND, ['type' => 'small'])
			->assertExitCode(0);

		// Get updated photo and check if size variant has been re-created
		/** @var \App\Models\Photo $photo2 */
		$photo2 = static::convertJsonToObject($this->photos_tests->get($photo1->id));
		self::assertNotNull($photo2->size_variants->small);
		self::assertEquals($photo1->size_variants->small->width, $photo2->size_variants->small->width);
		self::assertEquals($photo1->size_variants->small->height, $photo2->size_variants->small->height);
		self::assertFileExists(public_path($this->dropUrlPrefix($photo2->size_variants->small->url)));
	}
}
