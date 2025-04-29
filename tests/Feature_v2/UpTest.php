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

use App\Http\Middleware\AdminUserStatus;
use Tests\AbstractTestCase;

class UpTest extends AbstractTestCase
{
	public function testGet(): void
	{
        $this->withoutVite();
		$response = $this->withoutMiddleware(AdminUserStatus::class)->get('up');
		$this->assertOk($response);
	}
}