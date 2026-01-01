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

namespace Tests\ImageProcessing\Commands;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class MoveToS3Test extends BaseApiWithDataTest
{
	public const COMMAND = 'lychee:s3_migrate';

	public function testSuccess(): void
	{
		$this->artisan(self::COMMAND, [])
			->assertSuccessful();
	}
}