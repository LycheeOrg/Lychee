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

use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use function Safe\chmod;
use function Safe\fileperms;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatePhoto;
use Tests\Traits\RequiresExifTool;
use Tests\Traits\RequiresFFMpeg;

class FixPermissionsTest extends BaseApiWithDataTest
{
	use CreatePhoto;
	use RequiresExifTool;
	use RequiresFFMpeg;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresExifTool();
		$this->setUpRequiresFFMpeg();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresFFMpeg();
		$this->tearDownRequiresExifTool();
		parent::tearDown();
	}

	public const COMMAND = 'lychee:fix-permissions';

	/**
	 * Uploads a file, manipulates permissions of media file and checks whether they get fixed.
	 *
	 * @return void
	 */
	public function testFixPermissions(): void
	{
		if (config('filesystems.disks.images.visibility', 'public') !== 'public') {
			static::markTestSkipped('Wrong setting in .env file or configuration');
		}

		clearstatcache(true);

		$photo = $this->createPhoto(TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$filePath = public_path($this->dropUrlPrefix($photo['size_variants']['original']['url']));
		$dirPath = pathinfo($filePath, PATHINFO_DIRNAME);

		static::skipIfNotFileOwner($filePath);
		static::skipIfNotFileOwner($dirPath);

		chmod($filePath, 00400);
		chmod($dirPath, 00500);

		$this->artisan(self::COMMAND, ['--dry-run' => 0])->assertSuccessful();

		clearstatcache(true);
		self::assertEquals(00664, fileperms($filePath) & BasicPermissionCheck::READ_WRITE_ALL);
		self::assertEquals(02775, fileperms($dirPath) & BasicPermissionCheck::READ_WRITE_ALL);

		chmod($filePath, 00777);
		chmod($dirPath, 06777);

		$this->artisan(self::COMMAND, ['--dry-run' => 0])->assertSuccessful();

		clearstatcache(true);
		self::assertEquals(00664, fileperms($filePath) & BasicPermissionCheck::READ_WRITE_ALL);
		self::assertEquals(02775, fileperms($dirPath) & BasicPermissionCheck::READ_WRITE_ALL);
	}
}