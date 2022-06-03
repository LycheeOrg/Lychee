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
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class PhotosRotateTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testRotate(): void
	{
		$photos_tests = new PhotosUnitTest($this);

		AccessControl::log_as_id(0);

		$id = $photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);

		$response = $photos_tests->get($id);
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
					'filesize' => 21106422,
				],
			],
		]);

		$editor_enabled_value = Configs::get_value('editor_enabled');
		Configs::set('editor_enabled', '0');
		$response = $this->postJson('/api/PhotoEditor::rotate', [
			'photoID' => $id,
			'direction' => 1,
		]);
		$response->assertStatus(412);
		$response->assertSee('support for rotation disabled by configuration');

		Configs::set('editor_enabled', '1');
		$photos_tests->rotate('-1', 1, 422);
		$photos_tests->rotate($id, 'asdq', 422, 'The selected direction is invalid');
		$photos_tests->rotate($id, '2', 422, 'The selected direction is invalid');

		$response = $photos_tests->rotate($id, 1);
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

		$photos_tests->rotate($id, -1);

		/*
		* Check some Exif data
		*/
		$response = $photos_tests->get($id);
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

		$photos_tests->delete([$id]);

		// reset
		Configs::set('editor_enabled', $editor_enabled_value);

		AccessControl::logout();
	}
}
