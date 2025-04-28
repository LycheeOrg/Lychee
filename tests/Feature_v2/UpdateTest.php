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

namespace Tests\Feature_v2;

use App\Http\Middleware\MigrationStatus;
use Tests\Feature_v2\Base\BaseApiV2Test;

class UpdateTest extends BaseApiV2Test
{
	public function testForbiddenUnauthorized(): void
	{
		$response = $this->get('Update');
		$this->assertUnauthorized($response);

		$response = $this->get('migrate');
		$this->assertStatus($response, 307);

		$response = $this->withoutMiddleware(MigrationStatus::class)->get('migrate');
		$this->assertForbidden($response);
	}

	public function testForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->get('Update');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userMayUpload1)->get('migrate');
		$this->assertStatus($response, 307);

		$response = $this->withoutMiddleware(MigrationStatus::class)->get('migrate');
		$this->assertForbidden($response);
	}

	public function testGet(): void
	{
		$response = $this->actingAs($this->admin)->get('Update');
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->get('migrate');
		$this->assertStatus($response, 307);

		$response = $this->actingAs($this->admin)->withoutMiddleware(MigrationStatus::class)->get('migrate');
		$this->assertOk($response);
	}
}