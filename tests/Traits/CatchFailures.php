<?php

namespace Tests\Traits;

use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;

/**
 * This trait allows to retrieve the message returned by the back-end in case of unexpected results.
 * This provides more readable results than: "status code 500 does match expected status code 200".
 */
trait CatchFailures
{
	/**
	 * Some of the exceptions we get are expected. We silence then.
	 *
	 * @var array
	 */
	private array $catchFailureSilence = ["App\Exceptions\MediaFileOperationException"];

	protected function assertStatus(TestResponse $response, int|array $expectedStatusCode): void
	{
		if ($response->getStatusCode() === 500) {
			$exception = $response->json();
			if (in_array($exception['exception'], $this->catchFailureSilence, true)) {
				return;
			}
			$this->trimException($exception);
			dump($exception);
		}
		$expectedStatusCodeArray = is_int($expectedStatusCode) ? [$expectedStatusCode] : $expectedStatusCode;

		// We remove 204 as it does not have content
		// We remove 302 because it does not have json data.
		if (!in_array($response->getStatusCode(), [204, 302, ...$expectedStatusCodeArray], true)) {
			$exception = $response->json();
			$this->trimException($exception);
			dump($exception);
		}
		PHPUnit::assertContains($response->getStatusCode(), $expectedStatusCodeArray);
	}

	/**
	 * An exception is an array of the shape:
	 * array{message:string, exception:string, file:string, line:int, trace:array{}, previous_exception: obj }
	 * Unfortunately the trace contains the full call stack and dumping it completely does not add significant
	 * information. Most of the time only the first 3 values of the trace are of interest.
	 *
	 * For this reason this function only keeps the first 3 values of the trace of the exception returned.
	 *
	 * Additionally, this transformation is applied recursively on the previous_exception in the case of
	 * exception encapsulation.
	 *
	 * @param array $exception
	 *
	 * @return void
	 */
	private function trimException(array &$exception): void
	{
		if (isset($exception['trace'])) {
			$exception['trace'] = array_slice($exception['trace'], 0, 3);
		}

		if (isset($exception['previous_exception'])) {
			$this->trimException($exception['previous_exception']);
		}
	}

	protected function assertOk(TestResponse $response): void
	{
		$this->assertStatus($response, 200);
	}

	protected function assertUnprocessable(TestResponse $response): void
	{
		$this->assertStatus($response, 422);
	}

	protected function assertForbidden(TestResponse $response): void
	{
		$this->assertStatus($response, 403);
	}

	protected function assertUnauthorized(TestResponse $response): void
	{
		$this->assertStatus($response, 401);
	}

	protected function assertNoContent(TestResponse $response): void
	{
		$this->assertStatus($response, 204);
	}

	protected function assertRedirect(TestResponse $response): void
	{
		$this->assertStatus($response, 302);
	}

	protected function assertNotFound(TestResponse $response): void
	{
		$this->assertStatus($response, 404);
	}
}