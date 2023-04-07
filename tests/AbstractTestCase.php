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
use function Safe\copy;
use function Safe\json_decode;
use function Safe\tempnam;
use Tests\Feature\Traits\CatchFailures;

abstract class AbstractTestCase extends BaseTestCase
{
	use CreatesApplication;
	use CatchFailures;

	public const PATH_IMPORT_DIR = 'uploads/import/';

	public const MIME_TYPE_APP_PDF = 'application/pdf';
	public const MIME_TYPE_IMG_GIF = 'image/gif';
	public const MIME_TYPE_IMG_JPEG = 'image/jpeg';
	public const MIME_TYPE_IMG_PNG = 'image/png';
	public const MIME_TYPE_IMG_TIFF = 'image/tiff';
	public const MIME_TYPE_IMG_WEBP = 'image/webp';
	public const MIME_TYPE_IMG_XCF = 'image/x-xcf';
	public const MIME_TYPE_VID_MP4 = 'video/mp4';
	public const MIME_TYPE_VID_QUICKTIME = 'video/quicktime';

	public const SAMPLE_DOWNLOAD_JPG = 'https://github.com/LycheeOrg/Lychee/raw/master/tests/Samples/mongolia.jpeg';
	public const SAMPLE_DOWNLOAD_TIFF = 'https://github.com/LycheeOrg/Lychee/raw/master/tests/Samples/tiff.tif';

	public const SAMPLE_FILE_AARHUS = 'tests/Samples/aarhus.jpg';
	public const SAMPLE_FILE_ETTLINGEN = 'tests/Samples/ettlinger-alb.jpg';
	public const SAMPLE_FILE_GAMING_VIDEO = 'tests/Samples/gaming.mp4';
	public const SAMPLE_FILE_GIF = 'tests/Samples/gif.gif';
	public const SAMPLE_FILE_GMP_BROKEN_IMAGE = 'tests/Samples/google_motion_photo_broken.jpg';
	public const SAMPLE_FILE_GMP_IMAGE = 'tests/Samples/google_motion_photo.jpg';
	public const SAMPLE_FILE_HOCHUFERWEG = 'tests/Samples/hochuferweg.jpg';
	public const SAMPLE_FILE_MONGOLIA_IMAGE = 'tests/Samples/mongolia.jpeg';
	public const SAMPLE_FILE_NIGHT_IMAGE = 'tests/Samples/night.jpg';
	public const SAMPLE_FILE_ORIENTATION_180 = 'tests/Samples/orientation-180.jpg';
	public const SAMPLE_FILE_ORIENTATION_270 = 'tests/Samples/orientation-270.jpg';
	public const SAMPLE_FILE_ORIENTATION_90 = 'tests/Samples/orientation-90.jpg';
	public const SAMPLE_FILE_ORIENTATION_HFLIP = 'tests/Samples/orientation-hflip.jpg';
	public const SAMPLE_FILE_ORIENTATION_VFLIP = 'tests/Samples/orientation-vflip.jpg';
	public const SAMPLE_FILE_PDF = 'tests/Samples/pdf.pdf';
	public const SAMPLE_FILE_PNG = 'tests/Samples/png.png';
	public const SAMPLE_FILE_SUNSET_IMAGE = 'tests/Samples/fin de journée.jpg';
	public const SAMPLE_FILE_TIFF = 'tests/Samples/tiff.tif';
	public const SAMPLE_FILE_TRAIN_IMAGE = 'tests/Samples/train.jpg';
	public const SAMPLE_FILE_TRAIN_VIDEO = 'tests/Samples/train.mov';
	public const SAMPLE_FILE_UNDEFINED_EXIF_TAG = 'tests/Samples/undefined-exif-tag.jpg';
	public const SAMPLE_FILE_WEBP = 'tests/Samples/webp.webp';
	public const SAMPLE_FILE_XCF = 'tests/Samples/xcf.xcf';

	public const SAMPLE_FILES_2_MIME = [
		self::SAMPLE_FILE_AARHUS => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ETTLINGEN => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_GAMING_VIDEO => self::MIME_TYPE_VID_MP4,
		self::SAMPLE_FILE_GIF => self::MIME_TYPE_IMG_GIF,
		self::SAMPLE_FILE_GMP_BROKEN_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_GMP_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_HOCHUFERWEG => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_MONGOLIA_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_NIGHT_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_180 => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_270 => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_90 => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_HFLIP => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_VFLIP => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_PDF => self::MIME_TYPE_APP_PDF,
		self::SAMPLE_FILE_PNG => self::MIME_TYPE_IMG_PNG,
		self::SAMPLE_FILE_SUNSET_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_TIFF => self::MIME_TYPE_IMG_TIFF,
		self::SAMPLE_FILE_TRAIN_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_TRAIN_VIDEO => self::MIME_TYPE_VID_QUICKTIME,
		self::SAMPLE_FILE_UNDEFINED_EXIF_TAG => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_WEBP => self::MIME_TYPE_IMG_WEBP,
		self::SAMPLE_FILE_XCF => self::MIME_TYPE_IMG_XCF,
	];

	public const CONFIG_ALBUMS_SORTING_COL = 'sorting_albums_col';
	public const CONFIG_ALBUMS_SORTING_ORDER = 'sorting_albums_order';
	public const CONFIG_DOWNLOADABLE = 'grants_download';
	public const CONFIG_HAS_EXIF_TOOL = 'has_exiftool';
	public const CONFIG_HAS_FFMPEG = 'has_ffmpeg';
	public const CONFIG_HAS_IMAGICK = 'imagick';
	public const CONFIG_MAP_DISPLAY = 'map_display';
	public const CONFIG_MAP_DISPLAY_PUBLIC = 'map_display_public';
	public const CONFIG_MAP_INCLUDE_SUBALBUMS = 'map_include_subalbums';
	public const CONFIG_PHOTOS_SORTING_COL = 'sorting_photos_col';
	public const CONFIG_PHOTOS_SORTING_ORDER = 'sorting_photos_order';
	public const CONFIG_PUBLIC_HIDDEN = 'public_photos_hidden';
	public const CONFIG_PUBLIC_RECENT = 'public_recent';
	public const CONFIG_PUBLIC_SEARCH = 'public_search';
	public const CONFIG_PUBLIC_STARRED = 'public_starred';
	public const CONFIG_PUBLIC_ON_THIS_DAY = 'public_on_this_day';
	public const CONFIG_RAW_FORMATS = 'raw_formats';
	public const CONFIG_USE_JOB_QUEUES = 'use_job_queues';

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
		self::assertNotFalse($content);

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
		$tmpFilename = tempnam(sys_get_temp_dir(), 'lychee');
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
			->subDays(Configs::getValueAsInt('recent_age'))
			->setTimezone('UTC')
			->format('Y-m-d H:i:s');
		$recentFilter = function (Builder $query) use ($strRecent) {
			$query->where('created_at', '>=', $strRecent);
		};

		return Photo::query()->select('id')->where($recentFilter)->pluck('id');
	}
}
