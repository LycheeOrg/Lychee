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

namespace Tests\Feature_v2\Album;

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumsTest extends BaseApiV2Test
{
	public function testGetAnon(): void
	{
		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(0, $response->json('smart_albums'));
		$response->assertSee($this->album4->id);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [
				[
					'id' => $this->album4->id,
					'title' => $this->album4->title,
					'thumb' => [
						'id' => $this->photo4->id,
					],
					'is_nsfw' => false,
					'is_public' => true,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
			'shared_albums' => [],
			'config' => [
				'is_map_accessible' => false,
				'is_mod_frame_enabled' => false,
				'is_search_accessible' => false,
				'album_thumb_css_aspect_ratio' => 'aspect-square',
			],
		]);
	}

	public function testGetAsUserMayUpload1(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		self::assertCount(4, $response->json('smart_albums'));
		$response->assertSee($this->album1->id);
		$response->assertSee($this->album4->id);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [
				[
					'id' => $this->tagAlbum1->id,
					'title' => $this->tagAlbum1->title,
					'thumb' => [
						'id' => $this->photo1->id,
					],
				],
			],
			'albums' => [
				[
					'id' => $this->album1->id,
					'title' => $this->album1->title,
					'thumb' => [
						'id' => $this->photo1->id,
					],
					'is_nsfw' => false,
					'is_public' => false,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
			'shared_albums' => [
				[
					'id' => $this->album4->id,
					'title' => $this->album4->title,
					'thumb' => [
						'id' => $this->photo4->id,
					],
					'is_nsfw' => false,
					'is_public' => true,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
			'config' => [
				'is_map_accessible' => false,
				'is_mod_frame_enabled' => true,
				'is_search_accessible' => true,
				'album_thumb_css_aspect_ratio' => 'aspect-square',
			],
		]);
	}

	public function testGetAsUserMayUpload2(): void
	{
		$response = $this->actingAs($this->userMayUpload2)->getJson('Albums');
		$this->assertOk($response);
		$response->assertSee($this->album1->id);
		$response->assertSee($this->album2->id);
		$response->assertSee($this->album4->id);
		$response->assertJson([
			'smart_albums' => [],
			'tag_albums' => [],
			'albums' => [
				[
					'id' => $this->album2->id,
					'title' => $this->album2->title,
					'thumb' => [
						'id' => $this->photo2->id,
					],
					'is_nsfw' => false,
					'is_public' => false,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
			'shared_albums' => [
				[
					'id' => $this->album1->id,
					'title' => $this->album1->title,
					'thumb' => [
						'id' => $this->photo1->id,
					],
					'is_nsfw' => false,
					'is_public' => false,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
				[
					'id' => $this->album4->id,
					'title' => $this->album4->title,
					'thumb' => [
						'id' => $this->photo4->id,
					],
					'is_nsfw' => false,
					'is_public' => true,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
			'config' => [
				'is_map_accessible' => false,
				'is_mod_frame_enabled' => true,
				'is_search_accessible' => true,
				'album_thumb_css_aspect_ratio' => 'aspect-square',
			],
		]);
	}
}