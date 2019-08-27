<?php

namespace Tests\Feature;

use App\Configs;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Http\UploadedFile;
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
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id(0);

		// Make a copy of the image because import deletes the file and we want to be
		// able to use the test on a local machine and not just in CI.
		copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

		$file = new UploadedFile('public/uploads/import/night.jpg', 'night.jpg',
			'image/jpg', null, true);

		/**
		 * Test if we can upload.
		 */
		$response = $this->post('/api/Photo::add',
			[
				'albumID' => '0',
				'0' => $file,
			]);
		$id = $response->getContent();

		$response->assertStatus(200);
		$response->assertDontSee('Error');

		/**
		 * Check if we see the picture in unsorted.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => '0',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
		/**
		 * Check if we see the picture in recent.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 'r',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);
		/**
		 * Check if we see the picture in shared.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 's',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);
		/**
		 * Check if we see the picture in favorites.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 'f',
		]);
		$response->assertStatus(200);
		$response->assertDontSee($id);

		/**
		 * Try to set the title.
		 */
		$response = $this->post('/api/Photo::setTitle', [
			'title' => "Night in Ploumanac'h",
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to set the description.
		 */
		$response = $this->post('/api/Photo::setDescription', [
			'description' => 'A night photography',
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to set the stars.
		 */
		$response = $this->post('/api/Photo::setStar', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Check if we see the picture in favorite.
		 */
		$response = $this->post('/api/Album::get', [
			'albumID' => 'f',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);

		/**
		 * Try to set the tags.
		 */
		$response = $this->post('/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => 'night',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to set the photo to public.
		 */
		$response = $this->post('/api/Photo::setPublic', [
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to set the photo license type of the photo.
		 */
		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'reserved',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::get', [
			'albumID' => '0',
			'photoID' => $id,
		]);
		$response->assertStatus(200);

		/*
		 * Check some Exif data
		 */
		$response->assertJson([
			'aperture' => 'f/2.8',
			'cameraDate' => '1',
			'description' => 'A night photography',
			'focal' => '16 mm',
			'height' => 4480,
			'id' => $id,
			'iso' => '1250',
			'lens' => 'EF16-35mm f/2.8L USM',
			'license' => 'reserved',
			'make' => 'Canon',
			'model' => 'Canon EOS R',
			'public' => '1',
			'shutter' => '30 s',
			'size' => '20.1 MB',
			'small_dim' => '540x360',
			'star' => '1',
			'tags' => 'night',
			'medium_dim' => '1620x1080',
			'takedate' => '01 June 2019 at 01:28',
			'title' => "Night in Ploumanac'h",
			'type' => 'image/jpeg',
			'width' => 6720,
		]);

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'reserved',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC0',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC-BY',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC-BY-ND',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC-BY-SA',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC-BY-NC',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC-BY-NC-ND',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Photo::setLicense', [
			'photoID' => $id,
			'license' => 'CC-BY-NC-SA',
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::getRandom', []);
		$response->assertStatus(200);

		/**
		 * Try to delete the tag.
		 */
		$response = $this->post('/api/Photo::setTags', [
			'photoIDs' => $id,
			'tags' => null,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		$response = $this->post('/api/Album::add', [
			'title' => 'test_album_2',
			'parent_id' => '0',
		]);
		$response->assertOk();
		$response->assertDontSee('false');

		/**
		 * We also get the id of the album we just created.
		 */
		$albumID = $response->getContent();

		$response = $this->post('/api/Photo::setAlbum', [
			'photoIDs' => $id,
			'albumID' => $albumID,
		]);
		$response->assertOk();
		$response->assertDontSee('false');

		$response = $this->post('/api/Photo::duplicate', [
			'photoIDs' => $id,
		]);
		$response->assertOk();
		$response->assertDontSee('false');

		$response = $this->post('/api/Album::get', [
			'albumID' => $albumID,
		]);
		$response->assertOk();
		$content = $response->getContent();
		$array_content = json_decode($content);
		$this->assertEquals(2, count($array_content->photos));

		/**
		 * Try to delete the picture.
		 */
		$response = $this->post('/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');

		/**
		 * Try to get the picture again (should return an error).
		 */
		$response = $this->post('/api/Photo::get', [
			'albumID' => '0',
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('false');

		$response = $this->post('/api/Album::setPublic', [
			'full_photo' => 1,
			'albumID' => $albumID,
			'public' => 1,
			'visible' => 1,
			'downloadable' => 1,
		]);
		$response->assertOk();

		/**
		 * Actually try to display the picture.
		 */
		$response = $this->post('/api/Photo::getRandom', []);
		$response->assertStatus(200);

		// save initial value
		$init_config_value = Configs::get_value('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '1');
		$this->assertEquals(Configs::get_value('gen_demo_js'), '1');

		// check redirection
		$response = $this->get('/demo');
		$response->assertStatus(200);
		$response->assertViewIs('demo');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);

		$response = $this->post('/api/Album::delete', ['albumIDs' => $albumID]);
		$response->assertOk();
		$response->assertSee('true');

		$response = $this->get('/api/Photo::clearSymLink');
		$response->assertOk();
		$response->assertSee('true');
	}

	public function testUpload2()
	{
		// save initial value
		$init_config_value1 = Configs::get_value('SL_enable');
		$init_config_value2 = Configs::get_value('SL_for_admin');

		// set to 0
		Configs::set('SL_enable', '1');
		Configs::set('SL_for_admin', '1');
		$this->assertEquals(Configs::get_value('SL_enable'), '1');
		$this->assertEquals(Configs::get_value('SL_for_admin'), '1');

		// just redo the test above :'D
		$this->testUpload();

		// set back to initial value
		Configs::set('SL_enable', $init_config_value1);
		Configs::set('SL_for_admin', $init_config_value2);
	}
}
