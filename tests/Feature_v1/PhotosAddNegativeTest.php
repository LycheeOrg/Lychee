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

use function Safe\chmod;
use function Safe\copy;
use function Safe\scandir;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;
use Tests\Traits\InteractsWithFilesystemPermissions;
use Tests\Traits\InteractsWithRaw;

/**
 * Contains all tests which add photos to Lychee and are expected to fail.
 */
class PhotosAddNegativeTest extends BasePhotoTest
{
	use InteractsWithRaw;
	use InteractsWithFilesystemPermissions;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpInteractsWithFilesystemPermissions();
	}

	public function testNegativeUpload(): void
	{
		$this->photos_tests->wrong_upload();
		$this->photos_tests->wrong_upload2();
	}

	public function testImportViaDeniedMove(): void
	{
		static::skipIfNotFileOwner(static::importPath());

		// import the photo without the right to move the photo (aka delete the original)
		// For POSIX system, the right to create/rename/delete/edit meta-attributes
		// of a file is based on the write-privilege of the containing directory,
		// because all these operations require an update of a directory entry.
		// Making the file read-only is not sufficient to prevent deletion.
		copy(base_path(TestConstants::SAMPLE_FILE_NIGHT_IMAGE), static::importPath('read-only.jpg'));
		try {
			chmod(static::importPath('read-only.jpg'), 0444);
			chmod(static::importPath(), 0555);
			$this->photos_tests->importFromServer(static::importPath(), null, true, false, false);

			// check if the file is still there
			static::assertEquals(true, file_exists(static::importPath('read-only.jpg')));
		} finally {
			// re-grant file access
			chmod(static::importPath(), 0775);
			chmod(static::importPath('read-only.jpg'), 0664);
		}
	}

	public function testUploadWithReadOnlyStorage(): void
	{
		self::restrictDirectoryAccess(public_path('uploads/'));

		$this->photos_tests->upload(
			AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_NIGHT_IMAGE),
			null,
			500,
			'Unable to create a directory'
		);
	}

	/**
	 * Test upload of an unsupported raw image.
	 *
	 * @return void
	 */
	public function testRefusedRawUpload(): void
	{
		$acceptedRawFormats = static::getAcceptedRawFormats();
		try {
			static::setAcceptedRawFormats('');

			static::convertJsonToObject($this->photos_tests->upload(
				AbstractTestCase::createUploadedFile(TestConstants::SAMPLE_FILE_TIFF),
				null,
				422,
				'MediaFileUnsupportedException'
			));
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}

	/**
	 * Test import from URL of an unsupported raw image.
	 *
	 * This test is necessary in addition to uploading an unsupported raw
	 * file, because Lychee checks whether the format is supported prior to
	 * the download, i.e. before an actual file exists on the server and hence
	 * this check takes a different code path than the check after an upload.
	 *
	 * @return void
	 */
	public function testRefusedRawImportFormUrl(): void
	{
		$acceptedRawFormats = static::getAcceptedRawFormats();
		try {
			static::setAcceptedRawFormats('');

			$this->photos_tests->importFromUrl(
				[TestConstants::SAMPLE_DOWNLOAD_TIFF],
				null,
				422,
				'MediaFileUnsupportedException'
			);
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}

	/**
	 * Test import from URL of an unsupported raw image without file extension.
	 *
	 * We need this test because in case the file doesn't have an extension, we'll download the file
	 * and try to guess the extension.
	 *
	 * @return void
	 */
	public function testRefusedRawImportFormUrlWithoutExtension(): void
	{
		$acceptedRawFormats = static::getAcceptedRawFormats();
		try {
			static::setAcceptedRawFormats('');

			$this->photos_tests->importFromUrl(
				[TestConstants::SAMPLE_DOWNLOAD_TIFF_WITHOUT_EXTENSION],
				null,
				422,
				'MediaFileUnsupportedException'
			);
		} finally {
			static::setAcceptedRawFormats($acceptedRawFormats);
		}
	}

	/**
	 * Recursively restricts the access to the given directory.
	 *
	 * @param string $dirPath the directory path
	 *
	 * @return void
	 */
	protected static function restrictDirectoryAccess(string $dirPath): void
	{
		if (!is_dir($dirPath)) {
			return;
		}
		static::skipIfNotFileOwner($dirPath);

		$dirEntries = scandir($dirPath);
		foreach ($dirEntries as $dirEntry) {
			if (in_array($dirEntry, ['.', '..'], true)) {
				continue;
			}

			$dirEntryPath = $dirPath . DIRECTORY_SEPARATOR . $dirEntry;
			if (is_dir($dirEntryPath) && !is_link($dirEntryPath)) {
				self::restrictDirectoryAccess($dirEntryPath);
			}
		}

		\Safe\chmod($dirPath, 0555);
	}
}
