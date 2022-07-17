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
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class SearchTest extends TestCase
{
	public function testSearchPhotoByTitle(): void
	{
		AccessControl::log_as_id(0);

		$photos_tests = new PhotosUnitTest($this);

		$photoId = $photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photos_tests->set_title($photoId, 'photo search');

		$response = $this->postJson(
			'/api/Search::run',
			['term' => 'search']
		);
		$response->assertStatus(200);

		$response->assertJson([
			'photos' => [
				[
					'album_id' => null,
					'aperture' => 'f/2.8',
					'focal' => '16 mm',
					'id' => $photoId,
					'iso' => '1250',
					'lens' => 'EF16-35mm f/2.8L USM',
					'make' => 'Canon',
					'model' => 'Canon EOS R',
					'shutter' => '30 s',
					'title' => 'photo search',
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
							'filesize' => 22842265,
						],
					],
				],
			],
		]);

		$photos_tests->delete([$photoId]);

		AccessControl::logout();
	}

	public function testSearchPhotoByTag(): void
	{
		AccessControl::log_as_id(0);

		$photos_tests = new PhotosUnitTest($this);

		$photoId = $photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		);
		$photos_tests->set_tag([$photoId], ['search tag']);

		$response = $this->postJson(
			'/api/Search::run',
			['term' => 'search']
		);
		$response->assertStatus(200);

		$response->assertJson([
			'photos' => [
				[
					'album_id' => null,
					'aperture' => 'f/2.8',
					'focal' => '16 mm',
					'id' => $photoId,
					'iso' => '1250',
					'lens' => 'EF16-35mm f/2.8L USM',
					'make' => 'Canon',
					'model' => 'Canon EOS R',
					'shutter' => '30 s',
					'tags' => 'search tag',
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
							'filesize' => 22842265,
						],
					],
				],
			],
		]);

		$photos_tests->delete([$photoId]);

		AccessControl::logout();
	}

	public function testSearchAlbumByName(): void
	{
		AccessControl::log_as_id(0);

		$albums_test = new AlbumsUnitTest($this);

		/** @var string $id */
		$id = $albums_test->add(null, 'search')->offsetGet('id');

		$response = $this->postJson(
			'/api/Search::run',
			['term' => 'search']
		);
		$response->assertStatus(200);

		$response->assertJson([
			'albums' => [[
				'id' => $id,
				'title' => 'search',
			]],
		]);

		$albums_test->delete([$id]);

		AccessControl::logout();
	}

	public function testSearchAlbumByTag(): void
	{
		AccessControl::log_as_id(0);

		$albums_test = new AlbumsUnitTest($this);

		/** @var string $tagId */
		$tagId = $albums_test->addByTags('tag search', ['tag1', 'tag2'])->offsetGet('id');

		$response = $this->postJson(
			'/api/Search::run',
			['term' => 'search']
		);
		$response->assertStatus(200);

		$response->assertJson([
			'tag_albums' => [
				[
					'id' => $tagId,
					'title' => 'tag search',
				],
			],
		]);

		$albums_test->delete([$tagId]);

		AccessControl::logout();
	}
}
