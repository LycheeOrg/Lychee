<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	/**
	 * Visit the given URI with a GET request.
	 *
	 * Inspired by
	 * {@link \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests::get()}
	 * but transmits additional parameters in the query string.
	 *
	 * @param string $uri
	 * @param array  $queryParameters
	 * @param array  $headers
	 *
	 * @return TestResponse
	 */
	public function getWithParameters(string $uri, array $queryParameters = [], array $headers = []): TestResponse
	{
		$server = $this->transformHeadersToServerVars($headers);
		$cookies = $this->prepareCookiesForRequest();

		return $this->call('GET', $uri, $queryParameters, $cookies, [], $server);
	}

	/**
	 * Converts the JSON content of the response into a PHP standard object.
	 *
	 * @param TestResponse $response
	 *
	 * @return object
	 */
	protected static function convertJsonToObject(TestResponse $response): object
	{
		$content = $response->getContent();

		return json_decode($content);
	}
}
