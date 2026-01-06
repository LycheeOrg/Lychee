<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

namespace Tests\Feature_v2\Flow;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FlowInitTest extends BaseApiWithDataTest
{
	public function testGetAnonymous(): void
	{
		Configs::set('flow_public', false);

		$response = $this->getJson('Flow::init');
		$this->assertOk($response);
		$response->assertJson([
			'is_mod_flow_enabled' => false,
			'is_open_album_on_click' => false,
			'is_display_open_album_button' => false,
			'is_highlight_first_picture' => true,
			'is_image_header_enabled' => true,
			'image_header_cover' => 'cover',
			'image_header_height' => 24,
			'is_carousel_enabled' => true,
			'carousel_height' => 6,
			'is_blur_nsfw_enabled' => true,
			'is_compact_mode_enabled' => false,
		]);

		Configs::set('flow_public', true);

		$response = $this->getJson('Flow::init');
		$this->assertOk($response);
		$response->assertJson([
			'is_mod_flow_enabled' => true,
			'is_open_album_on_click' => false,
			'is_display_open_album_button' => false,
			'is_highlight_first_picture' => true,
			'is_image_header_enabled' => true,
			'image_header_cover' => 'cover',
			'image_header_height' => 24,
			'is_carousel_enabled' => true,
			'carousel_height' => 6,
			'is_blur_nsfw_enabled' => true,
			'is_compact_mode_enabled' => false,
		]);

		Configs::set('flow_public', false);
	}

	public function testGetUser(): void
	{
		$response = $this->actingAs($this->userLocked)->getJson('Flow::init');
		$this->assertOk($response);
		$response->assertJson([
			'is_mod_flow_enabled' => true,
			'is_open_album_on_click' => false,
			'is_display_open_album_button' => false,
			'is_highlight_first_picture' => true,
			'is_image_header_enabled' => true,
			'image_header_cover' => 'cover',
			'image_header_height' => 24,
			'is_carousel_enabled' => true,
			'carousel_height' => 6,
			'is_blur_nsfw_enabled' => true,
			'is_compact_mode_enabled' => false,
		]);
	}
}