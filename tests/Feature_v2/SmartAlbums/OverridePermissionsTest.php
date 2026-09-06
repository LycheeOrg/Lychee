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

namespace Tests\Feature_v2\SmartAlbums;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class OverridePermissionsTest extends BaseApiWithDataTest
{
	public function testSmartAlbums(): void
	{
		Configs::set('SA_override_visibility', true);
		Configs::set('hide_nsfw_in_smart_albums', false);

		$smart_albums = ['highlighted', 'recent', 'on_this_day', 'unsorted'];
		foreach ($smart_albums as $album_id) {
			$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
				'album_id' => $album_id,
				'is_public' => true,
				'is_link_required' => false,
				'is_nsfw' => false,
				'grants_download' => false,
				'grants_upload' => false,
				'grants_full_photo_access' => false,
			]);
			$this->assertCreated($response);
		}

		$response = $this->actingAs($this->userNoUpload)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJson(['smart_albums' => [
			['id' => 'unsorted', 'title' => 'Unsorted'],
			['id' => 'highlighted', 'title' => 'Highlighted'],
			['id' => 'recent', 'title' => 'Recent'],
			['id' => 'on_this_day', 'title' => 'On This Day'],
		]]);

		foreach ($smart_albums as $album_id) {
			$response = $this->actingAs($this->admin)->postJson('Album::updateProtectionPolicy', [
				'album_id' => $album_id,
				'is_public' => false,
				'is_link_required' => false,
				'is_nsfw' => false,
				'grants_download' => false,
				'grants_upload' => false,
				'grants_full_photo_access' => false,
			]);
			$this->assertCreated($response);
		}

		$response = $this->actingAs($this->userNoUpload)->getJson('Albums');
		$this->assertOk($response);
		$response->assertJsonCount(3, 'smart_albums');
	}
}