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

use App\Enum\JobStatus;
use App\Models\JobHistory;
use Tests\Feature_v2\Base\BaseApiV2Test;

class JobsTest extends BaseApiV2Test
{
	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::jobs', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Maintenance::jobs');
		$this->assertUnauthorized($response);
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Maintenance::jobs');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Maintenance::jobs');
		$this->assertForbidden($response);
	}

	public function testAdmin(): void
	{
		$jobHistory = new JobHistory();
		$jobHistory->owner_id = $this->admin->id;
		$jobHistory->job = 'Maintenance::jobs';
		$jobHistory->status = JobStatus::STARTED;
		$jobHistory->created_at = now();
		$jobHistory->updated_at = now();
		$jobHistory->save();

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::jobs');
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::jobs');
		$this->assertNoContent($response);

		// Do it again to cover all paths.
		$response = $this->actingAs($this->admin)->postJson('Maintenance::jobs');
		$this->assertNoContent($response);
	}
}