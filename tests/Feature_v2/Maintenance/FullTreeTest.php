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

namespace Tests\Feature_v2\Maintenance;

use Tests\Feature_v2\Base\BaseApiV2Test;

class FullTreeTest extends BaseApiV2Test
{
	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::fullTree', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Maintenance::fullTree', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Maintenance::fullTree', [
			'albums' => [
				[
					'id' => '123456789012345678901234',
					'_lft' => 1,
					'_rgt' => 2,
					'parent_id' => null,
				],
			],
		]);
		$this->assertUnauthorized($response);
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('/Maintenance::fullTree');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::fullTree', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::fullTree', [
			'albums' => [
				[
					'id' => '123456789012345678901234',
					'_lft' => 1,
					'_rgt' => 2,
					'parent_id' => null,
				],
			],
		]);
		$this->assertForbidden($response);
	}

	public function testAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('/Maintenance::fullTree');
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::fullTree', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::fullTree', [
			'albums' => [
				[
					'id' => $this->album1->id,
					'_lft' => 1,
					'_rgt' => 2,
					'parent_id' => null,
				],
			],
		]);
		$this->assertNoContent($response);
	}
}