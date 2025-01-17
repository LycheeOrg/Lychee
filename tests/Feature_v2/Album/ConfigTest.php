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

class ConfigTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);

		$response = $this->getJson('Gallery::getLayout');
		$this->assertOk($response);

		$response = $this->getJson('Gallery::getUploadLimits');
		$this->assertOk($response);

		$response = $this->getJson('Gallery::Footer');
		$this->assertOk($response);
	}
}