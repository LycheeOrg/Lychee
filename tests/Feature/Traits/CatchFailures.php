<?php

namespace Tests\Feature\Traits;

use Illuminate\Testing\TestResponse;

trait CatchFailures
{
	protected function assertStatus(TestResponse $response, int $expectedStatusCode): void
	{
		if ($response->getStatusCode() === 500) {
			$exception = $response->json();
			$this->trimException($exception);
			// $exception['trace'] = array_slice($exception['trace'], 0 ,3);
			dump($exception);
		}
		$response->assertStatus($expectedStatusCode);
	}

	private function trimException(array &$exception): void
	{
		$exception['trace'] = array_slice($exception['trace'], 0, 3);

		if ($exception['previous_exception'] !== null) {
			$exception['previous_exception'] = $this->trimException($exception['previous_exception']);
		}
	}

	protected function assertOk(TestResponse $response): void
	{
		$this->assertStatus($response, 200);
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
}