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

use App\Facades\AccessControl;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosRotateTest extends TestCase
{
	protected PhotosUnitTest $photos_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);

		// Assert that photo table is empty
		static::assertDatabaseCount('sym_links', 0);
		static::assertDatabaseCount('size_variants', 0);
		static::assertDatabaseCount('photos', 0);
	}

	public function tearDown(): void
	{
		// Clean up remaining stuff from tests
		DB::table('sym_links')->delete();
		DB::table('size_variants')->delete();
		DB::table('photos')->delete();
		self::cleanPublicFolders();

		AccessControl::logout();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testRotate(): void
	{
		$editor_enabled_value = Configs::get_value('editor_enabled');

		try {
			$id = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
			);

			$response = $this->photos_tests->get($id);
			/*
			* Check some Exif data
			*/
			$response->assertJson([
				'id' => $id,
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

			Configs::set('editor_enabled', '0');
			$response = $this->postJson('/api/PhotoEditor::rotate', [
				'photoID' => $id,
				'direction' => 1,
			]);
			$response->assertStatus(412);
			$response->assertSee('support for rotation disabled by configuration');

			Configs::set('editor_enabled', '1');
			$this->photos_tests->rotate('-1', 1, 422);
			$this->photos_tests->rotate($id, 'asdq', 422, 'The selected direction is invalid');
			$this->photos_tests->rotate($id, '2', 422, 'The selected direction is invalid');

			$response = $this->photos_tests->rotate($id, 1);
			/*
			* Check some Exif data
			*/
			$response->assertJson([
				'id' => $id,
				// 'filesize' => 21104156, // This changes during the image manipulation sadly.
				'size_variants' => [
					'small' => [
						'width' => 240,
						'height' => 360,
					],
					'medium' => [
						'width' => 720,
						'height' => 1080,
					],
					'original' => [
						'width' => 4480,
						'height' => 6720,
					],
				],
			]);

			$this->photos_tests->rotate($id, -1);

			/*
			* Check some Exif data
			*/
			$response = $this->photos_tests->get($id);
			$response->assertJson([
				'id' => $id,
				// 'filesize' => 21104156, // This changes during the image manipulation sadly.
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
					],
				],
			]);
		} finally {
			// reset
			Configs::set('editor_enabled', $editor_enabled_value);
		}
	}
}
