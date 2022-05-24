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

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	public const SAMPLE_FILE_NIGHT_IMAGE = 'tests/Samples/night.jpg';
	public const SAMPLE_FILE_MONGOLIA_IMAGE = 'tests/Samples/mongolia.jpeg';
	public const SAMPLE_FILE_TRAIN_IMAGE = 'tests/Samples/train.jpg';
	public const SAMPLE_FILE_TRAIN_VIDEO = 'tests/Samples/train.mov';
	public const SAMPLE_FILE_GMP_IMAGE = 'tests/Samples/google_motion_photo.jpg';
	public const SAMPLE_FILE_GAMING_VIDEO = 'tests/Samples/gaming.mp4';

	public const SAMPLE_FILES_2_MIME = [
		self::SAMPLE_FILE_NIGHT_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_MONGOLIA_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_TRAIN_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_TRAIN_VIDEO => 'video/quicktime',
		self::SAMPLE_FILE_GMP_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_GAMING_VIDEO => 'video/mp4',
	];

	/**
	 * Visit the given URI with a GET request.
	 *
	 * Inspired by
	 * {@link \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests::get()}
	 * but transmits additional parameters in the query string.
	 *
	 * @param string $uri
	 * @param array  $queryParameters
	 * @param array  $headers
	 *
	 * @return TestResponse
	 */
	public function getWithParameters(string $uri, array $queryParameters = [], array $headers = []): TestResponse
	{
		$server = $this->transformHeadersToServerVars($headers);
		$cookies = $this->prepareCookiesForRequest();

		return $this->call('GET', $uri, $queryParameters, $cookies, [], $server);
	}

	/**
	 * Converts the JSON content of the response into a PHP standard object.
	 *
	 * @param TestResponse $response
	 *
	 * @return object
	 */
	protected static function convertJsonToObject(TestResponse $response): object
	{
		$content = $response->getContent();

		return json_decode($content);
	}

	/**
	 * Creates a new "uploaded" file from one of the sample files.
	 *
	 * This method creates a copy of the sample file with a random file name
	 * and without any extension in the system's temporary directory.
	 * This mimics the exact behaviour of a true upload.
	 * In particular, the missing file extension is important as we don't
	 * have this for true uploads either, and without a file extension
	 * the MIME detector cannot rely on that.
	 *
	 * @param string $sampleFilePath the relative path to the sample file;
	 *                               use one of the `SAMPLE_FILE_...`-constants
	 *
	 * @return UploadedFile
	 */
	protected static function createUploadedFile(string $sampleFilePath): UploadedFile
	{
		$tmpFilename = \Safe\tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path($sampleFilePath), $tmpFilename);

		return new UploadedFile(
			$tmpFilename,
			pathinfo($sampleFilePath, PATHINFO_BASENAME),
			self::SAMPLE_FILES_2_MIME[$sampleFilePath],
			UPLOAD_ERR_OK,
			true
		);
	}

	/**
	 * Cleans the "public" folders 'uploads' and 'sym'.
	 *
	 * Removes all files from the directories except for sub-directories and
	 * 'index.html'.
	 *
	 * @return void
	 */
	protected static function cleanPublicFolders(): void
	{
		self::cleanupHelper(base_path('public/uploads/'));
		self::cleanupHelper(base_path('public/sym/'));
	}

	/**
	 * Cleans the designated directory recursively.
	 *
	 * Removes all files from the directories except for sub-directories and
	 * 'index.html'.
	 *
	 * @param string $dirPath the path of the directory
	 *
	 * @return void
	 */
	private static function cleanupHelper(string $dirPath): void
	{
		if (!is_dir($dirPath)) {
			return;
		}
		\Safe\chmod($dirPath, 0775);
		$dirEntries = scandir($dirPath);
		foreach ($dirEntries as $dirEntry) {
			if (in_array($dirEntry, ['.', '..', 'index.html', '.gitignore'])) {
				continue;
			}

			$dirEntryPath = $dirPath . DIRECTORY_SEPARATOR . $dirEntry;
			if (is_dir($dirEntryPath) && !is_link($dirEntryPath)) {
				self::cleanupHelper($dirEntryPath);
			}
			if (is_file($dirEntryPath) || is_link($dirEntryPath)) {
				unlink($dirEntryPath);
			}
		}
	}
}
