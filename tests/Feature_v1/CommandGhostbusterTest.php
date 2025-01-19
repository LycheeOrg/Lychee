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

use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;

class CommandGhostbusterTest extends Base\BasePhotoTest
{
	public const COMMAND = 'lychee:ghostbuster';

	public function testRemoveOrphanedFiles(): void
	{
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		));

		// The question mark operator is deliberately omitted for original
		// and thumb, because these size variants must be generated at least
		// otherwise we have nothing to test.
		$fileURLs = array_diff([
			$this->dropUrlPrefix($photo->size_variants->original->url),
			$this->dropUrlPrefix($photo->size_variants->medium2x?->url),
			$this->dropUrlPrefix($photo->size_variants->medium?->url),
			$this->dropUrlPrefix($photo->size_variants->small2x?->url),
			$this->dropUrlPrefix($photo->size_variants->small?->url),
			$this->dropUrlPrefix($photo->size_variants->thumb2x?->url),
			$this->dropUrlPrefix($photo->size_variants->thumb->url),
		], [null]);
		self::assertNotEmpty($fileURLs);

		// Remove photo and size variants from DB manually; note we must
		// not use an API call as this would also remove the files, and we
		// want to simulate orphaned files
		DB::table('size_variants')
			->where('photo_id', '=', $photo->id)
			->delete();
		DB::table('photos')
			->where('id', '=', $photo->id)
			->delete();

		// Ensure that files are still there
		foreach ($fileURLs as $fileURL) {
			self::assertFileExists(public_path($fileURL));
		}

		// Ghostbuster, ...
		$this->artisan(self::COMMAND, [
			'--dryrun' => 0,
		])
			->assertSuccessful();

		// Ensure that files are gone
		foreach ($fileURLs as $fileURL) {
			self::assertFileDoesNotExist(public_path($fileURL));
		}
	}

	public function testRemoveZombiePhotos(): void
	{
		/** @var \App\Models\Photo $photo */
		$photo = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE)
		));

		// The question mark operator is deliberately omitted for original
		// and thumb, because these size variants must be generated at least
		// otherwise we have nothing to test.
		$originalFileURL = $photo->size_variants->original->url;
		$fileURLs = array_diff([
			$this->dropUrlPrefix($originalFileURL),
			$this->dropUrlPrefix($photo->size_variants->medium2x?->url),
			$this->dropUrlPrefix($photo->size_variants->medium?->url),
			$this->dropUrlPrefix($photo->size_variants->small2x?->url),
			$this->dropUrlPrefix($photo->size_variants->small?->url),
			$this->dropUrlPrefix($photo->size_variants->thumb2x?->url),
			$this->dropUrlPrefix($photo->size_variants->thumb->url),
		], [null]);
		self::assertNotEmpty($fileURLs);

		// Remove original file
		\Safe\unlink(public_path($this->dropUrlPrefix($originalFileURL)));

		// Ghostbuster, ...
		$this->artisan(self::COMMAND, [
			'--dryrun' => 0,
			'--removeZombiePhotos' => 1,
		])
			->assertSuccessful();

		// Ensure that photo, size variants and all other size variants are gone
		self::assertEquals(
			0,
			DB::table('photos')
				->where('id', '=', $photo->id)
				->count()
		);
		self::assertEquals(
			0,
			DB::table('size_variants')
				->where('photo_id', '=', $photo->id)
				->count()
		);
		foreach ($fileURLs as $fileURL) {
			self::assertFileDoesNotExist(public_path($fileURL));
		}
	}
}
