<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Gallery;

use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Description limit exposed through Gallery::Init.
 */
class DescriptionMaxLengthInitTest extends BaseApiWithDataTest
{
	public function testDefault(): void
	{
		Config::set('features.description-max-length', 1000);
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson(['description_max_length' => 1000]);
	}

	public function testConfiguredValue(): void
	{
		Config::set('features.description-max-length', 5000);
		$response = $this->actingAs($this->userMayUpload1)->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson(['description_max_length' => 5000]);
	}
}
