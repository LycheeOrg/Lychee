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
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Assert;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;

class CommandEncodePlaceholdersTest extends BasePhotoTest
{
	public const COMMAND = 'lychee:encode_placeholders';
	public const GENERATE_THUMBS_COMMAND = 'lychee:generate_thumbs';

	public function testNoPlaceholdersUnencoded(): void
	{
		$this->artisan(self::COMMAND)
			->expectsOutput('No placeholders require encoding.')
			->assertExitCode(0);
	}

	public function testPlaceholderEncoding(): void
	{
		$originalConfig = Configs::getValueAsBool('low_quality_image_placeholder');
		Configs::set('low_quality_image_placeholder', true);

		/** @var \App\Models\Photo $photo1 */
		$photo1 = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		));

		// Remove the size variant "placeholder" from DB
		DB::table('size_variants')
			->where('photo_id', '=', $photo1->id)
			->where('type', '=', SizeVariantType::PLACEHOLDER)
			->delete();

		// Re-create it without encoding
		$this->artisan(self::GENERATE_THUMBS_COMMAND, ['type' => 'placeholder'])
			->assertExitCode(0);

		// Attempt to encode using command
		$this->artisan(self::COMMAND)
			->assertExitCode(0);

		// Get updated photo and check if placeholder was encoded
		/** @var \App\Models\Photo $photo2 */
		$photo2 = static::convertJsonToObject($this->photos_tests->get($photo1->id));
		// check for the file signature in the decoded base64 data.
		Assert::assertStringContainsString('WEBPVP8', \Safe\base64_decode($photo2->size_variants->placeholder->url));

		Configs::set('low_quality_image_placeholder', $originalConfig);
	}
}
