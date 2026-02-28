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

namespace Tests\Feature_v2\Maintenance;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class GenSizeVariantsTest extends BaseApiWithDataTest
{
	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::genSizeVariants', []);
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Maintenance::genSizeVariants', ['variant' => 5]);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Maintenance::genSizeVariants');
		$this->assertUnprocessable($response);

		$response = $this->postJson('Maintenance::genSizeVariants', ['variant' => 5]);
		$this->assertUnauthorized($response);
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Maintenance::genSizeVariants');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->getJsonWithData('Maintenance::genSizeVariants', ['variant' => 5]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::genSizeVariants');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::genSizeVariants', ['variant' => 5]);
		$this->assertForbidden($response);
	}

	public function testAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::genSizeVariants');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::genSizeVariants', ['variant' => 5]);
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::genSizeVariants', ['variant' => 2]);
		$this->assertOk($response);
		self::assertEquals(0, $response->json());

		$response = $this->actingAs($this->admin)->postJson('Maintenance::genSizeVariants');
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::genSizeVariants', ['variant' => 5]);
		$this->assertNoContent($response);
	}
}