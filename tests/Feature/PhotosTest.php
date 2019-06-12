<?php

namespace Tests\Feature;

use App\ModelFunctions\SessionFunctions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhotosTest extends TestCase
{
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testUpload()
	{
		$sessionFunctions = new SessionFunctions();
		$sessionFunctions->log_as_id(0);

		$response = $this->post('/api/Photo::add',
			[
				'albumID' => '0',
				'0' => UploadedFile::fake()->image('test.jpg', 4000, 4000),
			]);
		$id = $response->getContent();

		$response->assertStatus(200);
		$response->assertDontSee('Error');

		// try to load unsorted
		$response = $this->post('/api/Album::get', [
			'albumID' => '0',
		]);
		$response->assertStatus(200);
		$response->assertSee($id);

		$response = $this->post('/api/Photo::get', [
			'albumID' => '0',
			'photoID' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee($id);

		$response = $this->post('/api/Photo::delete', [
			'photoIDs' => $id,
		]);
		$response->assertStatus(200);
		$response->assertSee('true');
	}
}
