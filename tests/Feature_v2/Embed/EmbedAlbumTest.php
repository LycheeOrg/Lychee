<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Embed;

use App\Models\AccessPermission;
use App\Models\Album;
use Illuminate\Support\Facades\Hash;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Test the Embed API endpoint for external website integration.
 */
class EmbedAlbumTest extends BaseApiWithDataTest
{
	/**
	 * Test that public album can be embedded.
	 */
	public function testGetPublicAlbum(): void
	{
		// album4 is public (from test data)
		$response = $this->getJson('Embed/' . $this->album4->id);
		$this->assertOk($response);

		// Verify structure
		$response->assertJsonStructure([
			'album' => [
				'id',
				'title',
				'description',
				'photo_count',
				'copyright',
				'license',
			],
			'photos' => [
				'*' => [
					'id',
					'title',
					'description',
					'size_variants' => [
						'placeholder',
						'thumb',
						'thumb2x',
						'small',
						'small2x',
						'medium',
						'medium2x',
						'original' => [
							'width',
							'height',
						],
					],
					'exif' => [
						'make',
						'model',
						'lens',
						'iso',
						'aperture',
						'shutter',
						'focal',
						'taken_at',
					],
				],
			],
		]);

		// Verify album data
		$response->assertJson([
			'album' => [
				'id' => $this->album4->id,
				'title' => $this->album4->title,
			],
		]);
	}

	/**
	 * Test that private album cannot be embedded.
	 */
	public function testCannotGetPrivateAlbum(): void
	{
		// album1 is private (owned by user, not public)
		$response = $this->getJson('Embed/' . $this->album1->id);
		$this->assertForbidden($response);
	}

	/**
	 * Test that password-protected album cannot be embedded.
	 */
	public function testCannotGetPasswordProtectedAlbum(): void
	{
		// Make album public but password protected via access permission
		AccessPermission::factory()
			->public()
			->visible()
			->for_album($this->album1)
			->create([
				'password' => Hash::make('test123'),
			]);

		$response = $this->getJson('Embed/' . $this->album1->id);
		$this->assertForbidden($response);
	}

	/**
	 * Test that link-required album cannot be embedded.
	 */
	public function testCannotGetLinkRequiredAlbum(): void
	{
		// Make album public but link-required via access permission
		AccessPermission::factory()
			->public()
			->for_album($this->album1)
			->create([
				'is_link_required' => true,
			]);

		$response = $this->getJson('Embed/' . $this->album1->id);
		$this->assertForbidden($response);
	}

	/**
	 * Test that album with no photos can be embedded.
	 */
	public function testCanGetAlbumWithNoPhotos(): void
	{
		// Create a public album with no photos
		$emptyAlbum = Album::factory()
			->as_root()
			->owned_by($this->userMayUpload1)
			->create([
				'title' => 'Empty Album',
			]);

		AccessPermission::factory()
			->public()
			->visible()
			->for_album($emptyAlbum)
			->create();

		$response = $this->getJson('Embed/' . $emptyAlbum->id);
		$this->assertOk($response);

		$response->assertJson([
			'album' => [
				'id' => $emptyAlbum->id,
				'photo_count' => 0,
			],
			'photos' => [],
		]);

		$emptyAlbum->delete();
	}

	/**
	 * Test that non-existent album returns 404.
	 */
	public function testGetNonExistentAlbum(): void
	{
		$response = $this->getJson('Embed/non-existent-id');
		$this->assertNotFound($response);
	}
}
