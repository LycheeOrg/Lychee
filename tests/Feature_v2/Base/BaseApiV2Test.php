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

namespace Tests\Feature_v2\Base;

abstract class BaseApiV2Test extends BaseV2Test
{
	public const API_PREFIX = '/api/v2/';

	/**
	 * Visit the given URI with a GET request.
	 *
	 * @param string $uri
	 * @param array  $headers
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	public function get($uri, array $headers = [])
	{
		return parent::get(self::API_PREFIX . ltrim($uri, '/'), $headers);
	}

	/**
	 * Visit the given URI with a POST request.
	 *
	 * @param string $uri
	 * @param array  $data
	 * @param array  $headers
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	public function post($uri, array $data = [], array $headers = [])
	{
		$server = $this->transformHeadersToServerVars($headers);
		$cookies = $this->prepareCookiesForRequest();

		return $this->call('POST', self::API_PREFIX . ltrim($uri, '/'), $data, $cookies, [], $server);
	}

	/**
	 * Visit the given URI with a GET request, expecting a JSON response.
	 *
	 * @param string $uri
	 * @param array  $data
	 * @param array  $headers
	 * @param int    $options
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	public function getJsonWithData($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('GET', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}

	/**
	 * Visit the given URI with a GET request, expecting a JSON response.
	 *
	 * @param string $uri
	 * @param array  $headers
	 * @param int    $options
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	public function getJson($uri, array $headers = [], $options = 0)
	{
		return $this->json('GET', self::API_PREFIX . ltrim($uri, '/'), [], $headers, $options);
	}

	/**
	 * Visit the given URI with a POST request, expecting a JSON response.
	 *
	 * @param string $uri
	 * @param array  $data
	 * @param array  $headers
	 * @param int    $options
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	public function postJson($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('POST', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}

	/**
	 * Visit the given URI with a PATCH request, expecting a JSON response.
	 *
	 * @param string $uri
	 * @param array  $data
	 * @param array  $headers
	 * @param int    $options
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	public function patchJson($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('PATCH', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}
}
