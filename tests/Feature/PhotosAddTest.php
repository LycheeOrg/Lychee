<?php

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosAddTest extends TestCase
{
	protected PhotosUnitTest $photos_tests;
	protected AlbumsUnitTest $albums_tests;
	protected static UploadedFile $simpleJpegFile;
	protected static UploadedFile $appleLivePhotoFile;
	protected static UploadedFile $appleLiveVideoFile;
	protected static UploadedFile $googleMotionPhotoFile;
	protected bool $hasExifTools;
	protected int $hasExifToolsInit;
	protected bool $hasFFmpeg;
	protected int $hasFFmpegInit;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		/*
		 * We must use a temporary file name without/with a wrong file
		 * extension as a real upload would do in order to trigger the
		 * problematic code path.
		 */
		$tmpFilename = \Safe\tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path('tests/Samples/night.jpg'), $tmpFilename);
		self::$simpleJpegFile = new UploadedFile(
			$tmpFilename,
			'night.jpg',
			'image/jpeg',
			null,
			true
		);

		$tmpFilename = \Safe\tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path('tests/Samples/train.jpg'), $tmpFilename);
		self::$appleLivePhotoFile = new UploadedFile(
			$tmpFilename,
			'train.jpg',
			'image/jpeg',
			null,
			true
		);

		$tmpFilename = \Safe\tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path('tests/Samples/train.mov'), $tmpFilename);
		self::$appleLiveVideoFile = new UploadedFile(
			$tmpFilename,
			'train.mov',
			'video/quicktime',
			null,
			true
		);

		$tmpFilename = \Safe\tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path('tests/Samples/google_motion_photo.jpg'), $tmpFilename);
		self::$googleMotionPhotoFile = new UploadedFile(
			$tmpFilename,
			'google_motion_photo.jpg',
			'image/jpeg',
			null,
			true
		);
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);
		$this->albums_tests = new AlbumsUnitTest($this);

		$this->hasExifToolsInit = (int) Configs::get_value('has_exiftool', 2);
		Configs::set('has_exiftool', '2');
		$this->hasExifTools = Configs::hasExiftool();

		$this->hasFFmpegInit = (int) Configs::get_value('has_ffmpeg', 2);
		Configs::set('has_ffmpeg', '2');
		$this->hasFFmpeg = Configs::hasFFmpeg();

		AccessControl::log_as_id(0);
	}

	public function tearDown(): void
	{
		AccessControl::logout();

		Configs::set('has_exiftool', $this->hasExifToolsInit);
		Configs::set('has_ffmpeg', $this->hasFFmpegInit);

		parent::tearDown();
	}

	/**
	 * A simple upload of an ordinary photo.
	 *
	 * @return void
	 */
	public function testSimpleUpload()
	{
		$id = $this->photos_tests->upload(self::$simpleJpegFile);
		$response = $this->photos_tests->get($id);
		/*
		 * Check some Exif data
		 */
		$taken_at = Carbon::create(
			2019, 6, 1, 1, 28, 25, '+02:00'
		);
		$response->assertJson([
			'album_id' => null,
			'aperture' => 'f/2.8',
			'focal' => '16 mm',
			'id' => $id,
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'shutter' => '30 s',
			'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => 'night',
			'type' => 'image/jpeg',
			'size_variants' => [
				'small' => [
					'width' => 540,
					'height' => 360,
				],
				'medium' => [
					'width' => 1620,
					'height' => 1080,
				],
				'original' => [
					'width' => 6720,
					'height' => 4480,
					'filesize' => 21104156,
				],
			],
		]);
	}

	/**
	 * Tests Apple Live Photo upload.
	 *
	 * @return void
	 */
	public function testAppleLivePhotoUpload(): void
	{
		if (!$this->hasExifTools) {
			$this->markTestSkipped('Exiftool is not available. Test Skipped.');
		}

		$photo_id = $this->photos_tests->upload(self::$appleLivePhotoFile);
		$video_id = $this->photos_tests->upload(self::$appleLiveVideoFile);
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));
		$this->assertEquals($photo_id, $video_id);
		$this->assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
		$this->assertStringEndsWith('.mov', $photo->live_photo_url);
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
	}

	/**
	 * Tests Google Motion Photo upload.
	 *
	 * @return void
	 */
	public function testGoogleMotionPhotoUpload(): void
	{
		if (!$this->hasExifTools || !$this->hasFFmpeg) {
			$this->markTestSkipped('Exiftool or FFmpeg is not available. Test Skipped.');
		}

		$photo_id = $this->photos_tests->upload(self::$googleMotionPhotoFile);
		$photo = static::convertJsonToObject($this->photos_tests->get($photo_id));

		$this->assertStringEndsWith('.mov', $photo->live_photo_url);
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_DIRNAME), pathinfo($photo->size_variants->original->url, PATHINFO_DIRNAME));
		$this->assertEquals(pathinfo($photo->live_photo_url, PATHINFO_FILENAME), pathinfo($photo->size_variants->original->url, PATHINFO_FILENAME));
	}

	public function testImport()
	{
		// save initial value
		$init_config_value = Configs::get_value('import_via_symlink');

		// enable import via symlink option
		Configs::set('import_via_symlink', '1');
		$this->assertEquals('1', Configs::get_value('import_via_symlink'));

		$strRecent = Carbon::now()
			->subDays(intval(Configs::get_value('recent_age', '1')))
			->setTimezone('UTC')
			->format('Y-m-d H:i:s');
		$recentFilter = function (Builder $query) use ($strRecent) {
			$query->where('created_at', '>=', $strRecent);
		};

		$ids_before_import = Photo::query()->select('id')->where($recentFilter)->pluck('id');
		$num_before_import = $ids_before_import->count();

		// upload the photo
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));
		$streamed_response = $this->photos_tests->import(base_path('public/uploads/import/'));

		// check if the file is still there (without symlinks the photo would have been deleted)
		$this->assertEquals(true, file_exists('public/uploads/import/night.jpg'));

		$response = $this->albums_tests->get('recent');
		$responseObj = json_decode($response->getContent());
		$ids_after_import = (new BaseCollection($responseObj->photos))->pluck('id');
		$this->assertEquals(Photo::query()->where($recentFilter)->count(), $ids_after_import->count());
		$ids_to_delete = $ids_after_import->diff($ids_before_import)->all();
		$this->photos_tests->delete($ids_to_delete);

		$this->assertEquals($num_before_import, Photo::query()->where($recentFilter)->count());

		// set back to initial value
		Configs::set('import_via_symlink', $init_config_value);
	}
}
