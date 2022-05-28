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

use App\Models\Configs;
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	public const PATH_IMPORT_DIR = 'uploads/import/';

	public const SAMPLE_FILE_NIGHT_IMAGE = 'tests/Samples/night.jpg';
	public const SAMPLE_FILE_MONGOLIA_IMAGE = 'tests/Samples/mongolia.jpeg';
	public const SAMPLE_FILE_TRAIN_IMAGE = 'tests/Samples/train.jpg';
	public const SAMPLE_FILE_TRAIN_VIDEO = 'tests/Samples/train.mov';
	public const SAMPLE_FILE_GMP_IMAGE = 'tests/Samples/google_motion_photo.jpg';
	public const SAMPLE_FILE_GMP_BROKEN_IMAGE = 'tests/Samples/google_motion_photo_broken.jpg';
	public const SAMPLE_FILE_GAMING_VIDEO = 'tests/Samples/gaming.mp4';
	public const SAMPLE_FILE_ORIENTATION_90 = 'tests/Samples/orientation-90.jpg';
	public const SAMPLE_FILE_ORIENTATION_180 = 'tests/Samples/orientation-180.jpg';
	public const SAMPLE_FILE_ORIENTATION_270 = 'tests/Samples/orientation-270.jpg';
	public const SAMPLE_FILE_ORIENTATION_HFLIP = 'tests/Samples/orientation-hflip.jpg';
	public const SAMPLE_FILE_ORIENTATION_VFLIP = 'tests/Samples/orientation-vflip.jpg';
	public const SAMPLE_FILE_PNG = 'tests/Samples/png.png';
	public const SAMPLE_FILE_GIF = 'tests/Samples/gif.gif';
	public const SAMPLE_FILE_WEBP = 'tests/Samples/webp.webp';
	public const SAMPLE_FILE_PDF = 'tests/Samples/pdf.pdf';

	public const SAMPLE_FILES_2_MIME = [
		self::SAMPLE_FILE_NIGHT_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_MONGOLIA_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_TRAIN_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_TRAIN_VIDEO => 'video/quicktime',
		self::SAMPLE_FILE_GMP_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_GMP_BROKEN_IMAGE => 'image/jpeg',
		self::SAMPLE_FILE_GAMING_VIDEO => 'video/mp4',
		self::SAMPLE_FILE_ORIENTATION_90 => 'image/jpeg',
		self::SAMPLE_FILE_ORIENTATION_180 => 'image/jpeg',
		self::SAMPLE_FILE_ORIENTATION_270 => 'image/jpeg',
		self::SAMPLE_FILE_ORIENTATION_HFLIP => 'image/jpeg',
		self::SAMPLE_FILE_ORIENTATION_VFLIP => 'image/jpeg',
		self::SAMPLE_FILE_PNG => 'image/png',
		self::SAMPLE_FILE_GIF => 'image/gif',
		self::SAMPLE_FILE_WEBP => 'image/webp',
		self::SAMPLE_FILE_PDF => 'application/pdf',
	];

	public const CONFIG_HAS_FFMPEG = 'has_ffmpeg';
	public const CONFIG_HAS_EXIF_TOOL = 'has_exiftool';
	public const CONFIG_HAS_IMAGICK = 'imagick';
	public const CONFIG_RAW_FORMATS = 'raw_formats';

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

	protected static function importPath(string $path = ''): string
	{
		return public_path(self::PATH_IMPORT_DIR . $path);
	}

	/**
	 * @return BaseCollection<string> the IDs of recently added photos
	 */
	protected static function getRecentPhotoIDs(): BaseCollection
	{
		$strRecent = Carbon::now()
			->subDays(intval(Configs::get_value('recent_age', '1')))
			->setTimezone('UTC')
			->format('Y-m-d H:i:s');
		$recentFilter = function (Builder $query) use ($strRecent) {
			$query->where('created_at', '>=', $strRecent);
		};

		return Photo::query()->select('id')->where($recentFilter)->pluck('id');
	}
}
