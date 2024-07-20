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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumsTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJson('Albums::get');
		$response->assertOk();
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
					'is_nsfw_blurred' => false,
					'is_password_required' => false,
					'is_tag_album' => false,
					'has_subalbum' => true,
				],
			],
			'shared_albums' => [],
			'config' => [
				'is_map_accessible' => false,
				'is_mod_frame_enabled' => true,
				'is_search_accessible' => false,
				'album_thumb_css_aspect_ratio' => 'aspect-square',
				'album_subtitle_type' => 'oldstyle',
			],
		]);
	}
}