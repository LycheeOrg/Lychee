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

namespace Tests\Feature_v3\Base;

use Illuminate\Testing\TestResponse;

/**
 * Base class for API v3 Feature tests.
 *
 * Extends the v2 fixture graph by inheritance (zero duplication, zero edits
 * to the v2 base class) so v3 tests get the same users/albums/photos/
 * permissions graph documented on {@link \Tests\Feature_v2\Base\BaseApiWithDataTest}.
 *
 * IMPORTANT: Do NOT use the inherited v2-prefixed helpers
 * ({@see \Tests\Feature_v2\Base\BaseApiTest::getJson()}, `postJson()`, etc.) — they
 * hardcode the `/api/v2/` prefix via early-bound `self::API_PREFIX` and also
 * assume a JSON response, which does not fit this endpoint's binary success
 * response. Use {@see self::getV3()} instead.
 */
abstract class BaseApiWithDataTest extends \Tests\Feature_v2\Base\BaseApiWithDataTest
{
	public const API_V3_PREFIX = '/api/v3/';

	/**
	 * Visit the given v3 URI with a GET request.
	 *
	 * Uses Laravel's native `get()` (not `getJson()`) because this endpoint's
	 * success response is a raw binary file, not a JSON envelope.
	 *
	 * @param string               $uri
	 * @param array<string,string> $headers
	 *
	 * @return TestResponse
	 */
	public function getV3(string $uri, array $headers = []): TestResponse
	{
		return $this->withCredentials()->get(self::API_V3_PREFIX . ltrim($uri, '/'), $headers);
	}
}
