<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Feature_v2\Album;

use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class AlbumUpdateFocusTest extends BaseApiWithDataTest
{
	public function testUpdateAlbumWithEmptyFocus(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->userMayUpload1)->patchJson('Album::header', [
				'album_id' => $this->album1->id,
				'header_photo_focus' => [],
				'title_color' => null,
				'title_position' => null,
			]);
		$response->assertOk();
	}

	public function testUpdateAlbumFocusOutOfBounds(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->userMayUpload1)->patchJson('Album::header', [
				'album_id' => $this->album1->id,
				'header_photo_focus' => ['x' => -1.1, 'y' => 1.1],
				'title_color' => 'white',
				'title_position' => 'top_left',
			]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);

		// Assert clamped values
		$response->assertJsonPath('resource.preFormattedData.header_photo_focus.x', -1);
		$response->assertJsonPath('resource.preFormattedData.header_photo_focus.y', 1);
	}

	public function testUpdateAlbumWithFocus(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->userMayUpload1)->patchJson('Album::header', [
				'album_id' => $this->album1->id,
				'header_photo_focus' => ['x' => 0.5, 'y' => 0.5],
				'title_color' => null,
				'title_position' => null,
			]);
		$response->assertOk();
	}
}
