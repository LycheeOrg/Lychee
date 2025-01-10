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

class SharingUnitTest
{
	use CatchFailures;

	private AbstractTestCase $testCase;

	public function __construct(AbstractTestCase $testCase)
	{
		$this->testCase = $testCase;
	}

	/**
	 * List shares.
	 *
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function list(
		int $expectedStatusCode = 201,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson('/api/Sharing::list');
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}

	/**
	 * List shares.
	 *
	 * @param string[]    $albumIDs
	 * @param int[]       $userIDs
	 * @param int         $expectedStatusCode
	 * @param string|null $assertSee
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function add(
		array $albumIDs,
		array $userIDs,
		int $expectedStatusCode = 204,
		?string $assertSee = null,
	): TestResponse {
		$response = $this->testCase->postJson(
			'/api/Sharing::add', [
				'albumIDs' => $albumIDs,
				'userIDs' => $userIDs,
			]);
		$this->assertStatus($response, $expectedStatusCode);
		if ($assertSee !== null) {
			$response->assertSee($assertSee, false);
		}

		return $response;
	}
}
