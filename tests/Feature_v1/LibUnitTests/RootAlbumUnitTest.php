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

namespace Tests\Feature_v1\LibUnitTests;

use Illuminate\Testing\TestResponse;
use Tests\AbstractTestCase;
use Tests\Traits\CatchFailures;

class RootAlbumUnitTest
{
	use CatchFailures;

	private AbstractTestCase $testCase;

	public function __construct(AbstractTestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * Gets the root album.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 * @param string|null $assertDontSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function get(
		int $expectedStatusCode = 200,
		?string $assertSee = null,
		?string $assertDontSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::get');
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
		if ($assertDontSee !== null) {
			$response->assertDontSee($assertDontSee, false);
		}

		return $response;
	}

	/**
	 * Gets the album tree.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 * @param string|null $assertDontSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function getTree(
		int $expectedStatusCode = 200,
		?string $assertSee = null,
		?string $assertDontSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::tree');
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}
		if ($assertDontSee !== null) {
			$response->assertDontSee($assertDontSee, false);
		}

		return $response;
	}

	/**
	 * Gets the position data of photos within the root album.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function getPositionData(
		int $expectedStatusCode = 201,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::getPositionData');
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
