<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RootAlbumUnitTest
{
	private TestCase $testCase;

	public function __construct(TestCase $testCase)
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
	 * @return TestResponse
	 */
	public function get(
		int $expectedStatusCode = 200,
		?string $assertSee = null,
		?string $assertDontSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::get');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}
		if ($assertDontSee) {
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
	 * @return TestResponse
	 */
	public function getTree(
		int $expectedStatusCode = 200,
		?string $assertSee = null,
		?string $assertDontSee = null
	): TestResponse {
		$response = $this->testCase->postJson('/api/Albums::tree');
		$response->assertStatus($expectedStatusCode);
		if ($assertSee) {
			$response->assertSee($assertSee, false);
		}
		if ($assertDontSee) {
			$response->assertDontSee($assertDontSee, false);
		}

		return $response;
	}
}
