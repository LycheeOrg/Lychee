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

namespace Tests\Feature_v3\Photo;

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 081 (FR-081-07): a 360° photo cannot be rotated.
 */
class Photo360RotateTest extends BaseApiWithDataTest
{
	private bool $editor_enabled;

	public function setUp(): void
	{
		parent::setUp();
		// Configs::set() is not rolled back with the test transaction: restore it in tearDown().
		$this->editor_enabled = resolve(ConfigManager::class)->getValueAsBool('editor_enabled');
		Configs::set('editor_enabled', true);
	}

	public function tearDown(): void
	{
		Configs::set('editor_enabled', $this->editor_enabled);
		parent::tearDown();
	}

	public function testRotatingA360PhotoIsRejected(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create(['is_360' => true]);
		$checksum = $photo->checksum;

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::rotate', [
			'photo_id' => $photo->id,
			'direction' => 1,
			'from_id' => $album->id,
		]);

		$this->assertUnprocessable($response);
		$response->assertJsonPath('message', '360° photos cannot be rotated');
		self::assertSame($checksum, $photo->fresh()->checksum);
	}
}
