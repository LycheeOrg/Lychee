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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class StructOfArrayInitTest extends BaseApiWithDataTest
{
	public function testDisabled(): void
	{
		config(['features.struct-of-array' => false]);

		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'is_struct_of_array_enabled' => false,
		]);
	}

	public function testEnabled(): void
	{
		config(['features.struct-of-array' => true]);

		$response = $this->getJson('Gallery::Init');
		$this->assertOk($response);
		$response->assertJson([
			'is_struct_of_array_enabled' => true,
		]);

		config(['features.struct-of-array' => false]);
	}
}
