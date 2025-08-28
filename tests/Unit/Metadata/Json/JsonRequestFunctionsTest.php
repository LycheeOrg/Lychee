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

namespace Tests\Unit\Metadata\Json;

use App\Metadata\Json\JsonRequestFunctions;
use Tests\AbstractTestCase;

class JsonRequestFunctionsTest extends AbstractTestCase
{
	public function testInstanciate(): void
	{
		$update_request = new class('url', 5) extends JsonRequestFunctions {
			public function setData($data)
			{
				$this->data = $data;
			}
		};

		$update_request->setData('{}');
		$ret = $update_request->get_json();
		self::assertNull($ret);
	}
}
