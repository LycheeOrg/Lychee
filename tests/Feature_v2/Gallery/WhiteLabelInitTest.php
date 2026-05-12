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

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class WhiteLabelInitTest extends BaseApiWithDataTest
{
	public function testWhiteLabelDisabledByDefault(): void
	{
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'is_white_label_enabled' => false,
		]);
	}

	public function testWhiteLabelRemainsDisabledWhenFeatureEnabledWithoutSe(): void
	{
		// SE is not active in tests, so white label must evaluate to false
		// regardless of the features config value (SE gate in InitConfig).
		config(['features.white_label_enabled' => true]);

		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'is_white_label_enabled' => false,
		]);

		config(['features.white_label_enabled' => false]);
	}
}
