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

namespace Tests\Feature_v2\Install;

use App\Http\Middleware\InstallationStatus;
use Tests\AbstractTestCase;

class RequirementsTest extends AbstractTestCase
{
	public function testGet(): void
	{
		$response = $this->get('install/req');
		$this->assertForbidden($response);

		$response = $this->withoutMiddleware(InstallationStatus::class)->get('install/req');
		$this->assertOk($response);
	}
}