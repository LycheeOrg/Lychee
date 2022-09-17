<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use function Safe\chmod;
use Tests\TestCase;

class CommandFixPermissionsTest extends Base\PhotoTestBase
{
	public const COMMAND = 'lychee:fix-permissions';

	/**
	 * Uploads a file, manipulates permissions of media file and checks whether they get fixed.
	 *
	 * @return void
	 */
	public function testFixPermissions(): void
	{
		if (config('filesystems.images.visibility', 'public') !== 'public') {
			static::markTestSkipped('Wrong setting in .env file or configuration');
		}

		clearstatcache(true);

		$photo = static::convertJsonToObject($this->photos_tests->upload(
			static::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		));

		$filePath = public_path($photo->size_variants->original->url);
		$dirPath = pathinfo($filePath, PATHINFO_DIRNAME);

		static::skipIfNotFileOwner($filePath);
		static::skipIfNotFileOwner($dirPath);

		chmod($filePath, 00400);
		chmod($dirPath, 00500);

		$this->artisan(self::COMMAND, [
			'--dry-run' => 0,
		])->assertSuccessful();

		clearstatcache(true);
		static::assertEquals(00664, fileperms($filePath) & 07777, $filePath);
		static::assertEquals(02775, fileperms($dirPath) & 07777, $dirPath);

		chmod($filePath, 00777);
		chmod($dirPath, 06777);

		$this->artisan(self::COMMAND, [
			'--dry-run' => 0,
		])->assertSuccessful();

		clearstatcache(true);
		static::assertEquals(00664, fileperms($filePath) & 07777);
		static::assertEquals(02775, fileperms($dirPath) & 07777);
	}
}
