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

	public function __construct(AbstractTestCase $test_case)
	{
		$this->testCase = $test_case;
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
		int $expected_status_code = 200,
		?string $assert_see = null,
		?string $assert_dont_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::get');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}
		if ($assert_dont_see !== null) {
			$response->assertDontSee($assert_dont_see, false);
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
		int $expected_status_code = 200,
		?string $assert_see = null,
		?string $assert_dont_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::tree');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}
		if ($assert_dont_see !== null) {
			$response->assertDontSee($assert_dont_see, false);
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
		int $expected_status_code = 201,
		?string $assert_see = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::getPositionData');
		$this->assertStatus($response, $expected_status_code);
		if ($assert_see !== null) {
			$response->assertSee($assert_see, false);
		}

		return $response;
	}
}
