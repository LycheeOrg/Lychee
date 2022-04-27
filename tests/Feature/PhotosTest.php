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

class PhotosTest extends TestCase
{
	/**
	 * Test photo operations.
	 *
	 * @return void
	 */
	public function testUpload()
	{
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

		AccessControl::log_as_id(0);

		/*
		 * Make a copy of the image because import deletes the file, and we want to be
		 * able to use the test on a local machine and not just in CI.
		 */
		copy(base_path('tests/Samples/night.jpg'), base_path('public/uploads/import/night.jpg'));

		$file = new UploadedFile(
			base_path('public/uploads/import/night.jpg'),
			'night.jpg',
			'image/jpeg',
			null,
			true
		);

		$id = $photos_tests->upload($file);

		$photos_tests->get($id);

		$photos_tests->see_in_unsorted($id);
		$photos_tests->see_in_recent($id);
		$photos_tests->dont_see_in_shared($id);
		$photos_tests->dont_see_in_favorite($id);

		$photos_tests->set_title($id, "Night in Ploumanac'h");
		$photos_tests->set_description($id, 'A night photography');
		$photos_tests->set_star([$id], true);
		$photos_tests->set_tag([$id], ['night']);
		$photos_tests->set_public($id, true);
		$photos_tests->set_license($id, 'WTFPL', 422, 'The given data was invalid');
		$photos_tests->set_license($id, 'CC0');
		$photos_tests->set_license($id, 'CC-BY-1.0');
		$photos_tests->set_license($id, 'CC-BY-2.0');
		$photos_tests->set_license($id, 'CC-BY-2.5');
		$photos_tests->set_license($id, 'CC-BY-3.0');
		$photos_tests->set_license($id, 'CC-BY-4.0');
		$photos_tests->set_license($id, 'CC-BY-ND-1.0');
		$photos_tests->set_license($id, 'CC-BY-ND-2.0');
		$photos_tests->set_license($id, 'CC-BY-ND-2.5');
		$photos_tests->set_license($id, 'CC-BY-ND-3.0');
		$photos_tests->set_license($id, 'CC-BY-ND-4.0');
		$photos_tests->set_license($id, 'CC-BY-SA-1.0');
		$photos_tests->set_license($id, 'CC-BY-SA-2.0');
		$photos_tests->set_license($id, 'CC-BY-SA-2.5');
		$photos_tests->set_license($id, 'CC-BY-SA-3.0');
		$photos_tests->set_license($id, 'CC-BY-SA-4.0');
		$photos_tests->set_license($id, 'CC-BY-NC-1.0');
		$photos_tests->set_license($id, 'CC-BY-NC-2.0');
		$photos_tests->set_license($id, 'CC-BY-NC-2.5');
		$photos_tests->set_license($id, 'CC-BY-NC-3.0');
		$photos_tests->set_license($id, 'CC-BY-NC-4.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-1.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-2.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-2.5');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-3.0');
		$photos_tests->set_license($id, 'CC-BY-NC-ND-4.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-1.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-2.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-2.5');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-3.0');
		$photos_tests->set_license($id, 'CC-BY-NC-SA-4.0');
		$photos_tests->set_license($id, 'reserved');

		$photos_tests->see_in_favorite($id);
		$photos_tests->see_in_shared($id);
		$response = $photos_tests->get($id);
		$photos_tests->download($id);

		/*
		 * Check some Exif data
		 */
		$taken_at = Carbon::create(
			2019, 6, 1, 1, 28, 25, '+02:00'
		);
		$response->assertJson([
			'album_id' => null,
			'aperture' => 'f/2.8',
			'description' => 'A night photography',
			'focal' => '16 mm',
			'id' => $id,
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'license' => 'reserved',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'is_public' => 1,
			'shutter' => '30 s',
			'is_starred' => true,
			'tags' => ['night'],
			'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => "Night in Ploumanac'h",
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

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->postJson('/api/Photo::getRandom');
		$response->assertOk();

		/*
		 * Erase tag
		 */
		$photos_tests->set_tag([$id], []);

		/**
		 * We now test interaction with albums.
		 */
		$albumID = $albums_tests->add(null, 'test_album_2')->offsetGet('id');
		$photos_tests->set_album('-1', [$id], 422);
		$photos_tests->set_album($albumID, [$id]);
		$albums_tests->download($albumID);
		$photos_tests->dont_see_in_unsorted($id);

		/**
		 * Test duplication, the duplicate should be completely identical
		 * except for the IDs.
		 */
		$response = $photos_tests->duplicate([$id], $albumID);
		$response->assertJson([
			'album_id' => $albumID,
			'aperture' => 'f/2.8',
			'description' => 'A night photography',
			'focal' => '16 mm',
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'license' => 'reserved',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'is_public' => 1,
			'shutter' => '30 s',
			'is_starred' => true,
			'tags' => [],
			'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
			'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
			'title' => "Night in Ploumanac'h",
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

		/**
		 * Get album which should contain both photos.
		 */
		$album = $this->asObject($albums_tests->get($albumID));
		$this->assertCount(2, $album->photos);

		$ids = [];
		$ids[0] = $album->photos[0]->id;
		$ids[1] = $album->photos[1]->id;
		$photos_tests->delete([$ids[0]]);
		$photos_tests->get($ids[0], 404);

		$photos_tests->dont_see_in_recent($ids[0]);
		$photos_tests->dont_see_in_unsorted($ids[1]);

		$albums_tests->set_protection_policy($albumID);

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->postJson('/api/Photo::getRandom');
		$response->assertOk();

		// delete the picture after displaying it
		$photos_tests->delete([$ids[1]]);
		$photos_tests->get($ids[1], 404);
		$album = $this->asObject($albums_tests->get($albumID));
		$this->assertCount(0, $album->photos);

		// save initial value
		$init_config_value = Configs::get_value('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '1');
		$this->assertEquals('1', Configs::get_value('gen_demo_js'));

		// check redirection
		$response = $this->get('/demo');
		$response->assertOk();
		$response->assertViewIs('demo');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);

		$albums_tests->delete([$albumID]);

		$response = $this->postJson('/api/Photo::clearSymLink');
		$response->assertNoContent();

		AccessControl::logout();
	}

	/**
	 * Test live photo upload.
	 *
	 * @return void
	 */
	public function testLivePhotoUpload()
	{
		$photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);
		// MUST use exiftool to get live photo metadata
		$init_config_value = Configs::get_value('has_exiftool');

		// we set the value to 2to force the check.
		Configs::set('has_exiftool', '2');

		if (Configs::hasExiftool()) {
			/*
			* Make a copy of the image because import deletes the file, and we want to be
			* able to use the test on a local machine and not just in CI.
			*/
			copy(base_path('tests/Samples/train.jpg'), base_path('public/uploads/import/train.jpg'));
			copy(base_path('tests/Samples/train.mov'), base_path('public/uploads/import/train.mov'));

			$photo_file = new UploadedFile(
				'public/uploads/import/train.jpg',
				'train.jpg',
				'image/jpeg',
				null,
				true
			);

			$video_file = new UploadedFile(
				'public/uploads/import/train.mov',
				'train.mov',
				'video/quicktime',
				null,
				true
			);

			$photo_id = $photos_tests->upload($photo_file);
			$video_id = $photos_tests->upload($video_file);

			$photo = $this->asObject($photos_tests->get($photo_id));

			$this->assertEquals($photo_id, $video_id);
			$this->assertEquals('E905E6C6-C747-4805-942F-9904A0281F02', $photo->live_photo_content_id);
			$this->assertStringEndsWith('.mov', $photo->live_photo_url);
		} else {
			$this->markTestSkipped('Exiftool is not available. Test Skipped.');
		}
		Configs::set('has_exiftool', $init_config_value);
		AccessControl::logout();
	}

	public function testTrueNegative()
	{
		$photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);

		$photos_tests->wrong_upload();
		$photos_tests->wrong_upload2();
		$photos_tests->get('-1', 422);
		$photos_tests->get('abcdefghijklmnopxyrstuvx', 404);
		$photos_tests->set_description('-1', 'test', 422);
		$photos_tests->set_description('abcdefghijklmnopxyrstuvx', 'test', 404);
		$photos_tests->set_public('-1', true, 422);
		$photos_tests->set_public('abcdefghijklmnopxyrstuvx', true, 404);
		$photos_tests->set_album('-1', ['-1'], 422);
		$photos_tests->set_album('abcdefghijklmnopxyrstuvx', ['-1'], 422);
		$photos_tests->set_album('-1', ['abcdefghijklmnopxyrstuvx'], 422);
		$photos_tests->set_album('abcdefghijklmnopxyrstuvx', ['abcdefghijklmnopxyrstuvx'], 404);
		$photos_tests->set_license('-1', 'CC0', 422);
		$photos_tests->set_license('abcdefghijklmnopxyrstuvx', 'CC0', 404);

		AccessControl::logout();
	}

	public function testUpload2()
	{
		// save initial value
		$init_config_value1 = Configs::get_value('SL_enable');
		$init_config_value2 = Configs::get_value('SL_for_admin');

		// set to 0
		Configs::set('SL_enable', '1');
		Configs::set('SL_for_admin', '1');
		$this->assertEquals('1', Configs::get_value('SL_enable'));
		$this->assertEquals('1', Configs::get_value('SL_for_admin'));

		// just redo the test above :'D
		$this->testUpload();

		// set back to initial value
		Configs::set('SL_enable', $init_config_value1);
		Configs::set('SL_for_admin', $init_config_value2);
	}

	public function testImport()
	{
		$photos_tests = new PhotosUnitTest($this);
		$albums_tests = new AlbumsUnitTest($this);

		AccessControl::log_as_id(0);

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
		$streamed_response = $photos_tests->import(base_path('public/uploads/import/'));

		// check if the file is still there (without symlinks the photo would have been deleted)
		$this->assertEquals(true, file_exists('public/uploads/import/night.jpg'));

		$response = $albums_tests->get('recent');
		$responseObj = json_decode($response->getContent());
		$ids_after_import = (new BaseCollection($responseObj->photos))->pluck('id');
		$this->assertEquals(Photo::query()->where($recentFilter)->count(), $ids_after_import->count());
		$ids_to_delete = $ids_after_import->diff($ids_before_import)->all();
		$photos_tests->delete($ids_to_delete);

		$this->assertEquals($num_before_import, Photo::query()->where($recentFilter)->count());

		// set back to initial value
		Configs::set('import_via_symlink', $init_config_value);

		AccessControl::logout();
	}

	private function asObject($response)
	{
		$content = $response->getContent();

		return json_decode($content);
	}
}
