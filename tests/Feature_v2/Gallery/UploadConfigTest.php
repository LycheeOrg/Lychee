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

namespace Tests\Feature_v2\Gallery;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UploadConfigTest extends BaseApiWithDataTest
{
	public function testWatermarkerStatusWhenDisabled(): void
	{
		Configs::set('watermark_enabled', false);

		$response = $this->getJson('Gallery::getUploadLimits');
		$this->assertOk($response);
		$response->assertJson([
			'can_watermark_optout' => false,
		]);
	}

	public function testWatermarkerStatusWhenEnabledWithPhotoId(): void
	{
		$this->requireSe();

		Configs::set('watermark_enabled', true);
		Configs::set('watermark_photo_id', $this->photo1->id);
		Configs::set('imagick', true);
		Configs::set('watermark_optout_disabled', false);

		$response = $this->getJson('Gallery::getUploadLimits');
		$this->assertOk($response);
		$response->assertJson([
			'can_watermark_optout' => true,
		]);
	}

	public function testWatermarkerStatusWhenMissingPhotoId(): void
	{
		Configs::set('watermark_enabled', true);
		Configs::set('watermark_photo_id', '');
		Configs::set('imagick', true);

		$response = $this->getJson('Gallery::getUploadLimits');
		$this->assertOk($response);
		$response->assertJson([
			'can_watermark_optout' => false,
		]);
	}

	public function testWatermarkerStatusWhenImagickDisabled(): void
	{
		Configs::set('watermark_enabled', true);
		Configs::set('watermark_photo_id', $this->photo1->id);
		Configs::set('imagick', false);

		$response = $this->getJson('Gallery::getUploadLimits');
		$this->assertOk($response);
		$response->assertJson([
			'can_watermark_optout' => false,
		]);
	}

	public function testCannotOptOutWhenDisabledByAdmin(): void
	{
		$this->requireSe();

		Configs::set('watermark_enabled', true);
		Configs::set('watermark_photo_id', $this->photo1->id);
		Configs::set('imagick', true);
		Configs::set('watermark_optout_disabled', true);

		$response = $this->getJson('Gallery::getUploadLimits');
		$this->assertOk($response);
		$response->assertJson([
			'can_watermark_optout' => false,
		]);
	}
}
