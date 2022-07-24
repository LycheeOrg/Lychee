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

use Tests\Feature\Base\PhotoTestBase;
use Tests\TestCase;

class SearchTest extends PhotoTestBase
{
	public function testSearchPhotoByTitle(): void
	{
		$photoId1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photoId2 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$this->photos_tests->set_title($photoId1, 'photo search');
		$this->photos_tests->set_title($photoId2, 'do not find me');

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
					'id' => $photoId1,
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
							'filesize' => 21106422,
						],
					],
				],
			],
		]);

		$response->assertJsonMissing([
			'title' => 'do not find me',
		]);

		$response->assertJsonMissing([
			'id' => $photoId2,
		]);
	}

	public function testSearchPhotoByTag(): void
	{
		$photoId1 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_NIGHT_IMAGE)
		)->offsetGet('id');
		$photoId2 = $this->photos_tests->upload(
			TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');
		$this->photos_tests->set_title($photoId1, 'photo search');
		$this->photos_tests->set_title($photoId2, 'do not find me');
		$this->photos_tests->set_tag([$photoId1], ['search tag']);
		$this->photos_tests->set_tag([$photoId2], ['other tag']);

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
					'id' => $photoId1,
					'iso' => '1250',
					'lens' => 'EF16-35mm f/2.8L USM',
					'make' => 'Canon',
					'model' => 'Canon EOS R',
					'shutter' => '30 s',
					'tags' => ['search tag'],
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
							'filesize' => 21106422,
						],
					],
				],
			],
		]);

		$response->assertJsonMissing([
			'title' => 'do not find me',
		]);

		$response->assertJsonMissing([
			'tags' => ['other tag'],
		]);

		$response->assertJsonMissing([
			'id' => $photoId2,
		]);
	}

	public function testSearchAlbumByTitle(): void
	{
		/** @var string $albumId1 */
		$albumId1 = $this->albums_tests->add(null, 'search')->offsetGet('id');
		/** @var string $albumId2 */
		$albumId2 = $this->albums_tests->add(null, 'other')->offsetGet('id');

		$response = $this->postJson(
			'/api/Search::run',
			['term' => 'search']
		);
		$response->assertStatus(200);

		$response->assertJson([
			'albums' => [[
				'id' => $albumId1,
				'title' => 'search',
			]],
		]);

		$response->assertJsonMissing([
			'title' => 'other',
		]);

		$response->assertJsonMissing([
			'id' => $albumId2,
		]);

		$this->albums_tests->delete([$albumId1, $albumId2]);
	}

	public function testSearchTagAlbumByTitle(): void
	{
		/** @var string $tagAlbumId1 */
		$tagAlbumId1 = $this->albums_tests->addByTags('tag search', ['tag1', 'tag2'])->offsetGet('id');
		/** @var string $tagAlbumId2 */
		$tagAlbumId2 = $this->albums_tests->addByTags('tag other', ['tag3'])->offsetGet('id');

		$response = $this->postJson(
			'/api/Search::run',
			['term' => 'search']
		);
		$response->assertStatus(200);

		$response->assertJson([
			'tag_albums' => [
				[
					'id' => $tagAlbumId1,
					'title' => 'tag search',
				],
			],
		]);

		$response->assertJsonMissing([
			'title' => 'tag other',
		]);

		$response->assertJsonMissing([
			'id' => $tagAlbumId2,
		]);

		$this->albums_tests->delete([$tagAlbumId1]);
	}
}
