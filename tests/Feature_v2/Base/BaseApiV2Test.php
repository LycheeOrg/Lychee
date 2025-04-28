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

namespace Tests\Feature_v2\Base;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Uri;
use Illuminate\Testing\TestResponse;
use Safe\Exceptions\FilesystemException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

abstract class BaseApiV2Test extends BaseV2Test
{
	public const API_PREFIX = '/api/v2/';

	/**
	 * Visit the given URI with a GET request, expecting a JSON response.
	 *
	 * @param string $uri
	 * @param array  $data
	 * @param array  $headers
	 * @param int    $options
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function getJsonWithData($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('GET', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}

	/**
	 * Visit the given URI with a GET request, expecting a JSON response.
	 *
	 * @param Uri|string $uri
	 * @param array      $headers
	 * @param int        $options
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function getJson($uri, array $headers = [], $options = 0)
	{
		return $this->json('GET', self::API_PREFIX . ltrim($uri, '/'), [], $headers, $options);
	}

	/**
	 * Visit the given URI with a POST request, expecting a JSON response.
	 *
	 * @param Uri|string $uri
	 * @param array      $data
	 * @param array      $headers
	 * @param int        $options
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function postJson($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('POST', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}

	/**
	 * Execute a POST request with file upload.
	 * This is different than the usual JSON request as it uses multipart/form-data.
	 * 
	 * @param string $uri 
	 * @param null|string $filename 
	 * @param null|array $data 
	 * @param null|array $headers 
	 * @return TestResponse 
	 */
	public function upload(
		string $uri,
		?string $filename = null,
		?string $album_id = null,
		int $file_last_modified_time = 1678824303000,
		?array $data = null,
		?array $headers = null
	): TestResponse {
		$data ??= [
			'album_id' => $album_id,
			'file' => static::createUploadedFile($filename),
			'file_last_modified_time' => $file_last_modified_time,
			'file_name' => $filename,
			'uuid_name' => '',
			'extension' => '',
			'chunk_number' => 1,
			'total_chunks' => 1,
		];
		$headers ??= [
			'CONTENT_TYPE' => 'multipart/form-data',
			'Accept' => 'application/json',
		];

		return $this->post(
			uri: self::API_PREFIX . ltrim($uri, '/'),
			data: $data,
			headers: $headers
		);
	}

	/**
	 * Visit the given URI with a PATCH request, expecting a JSON response.
	 *
	 * @param Uri|string $uri
	 * @param array      $data
	 * @param array      $headers
	 * @param int        $options
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function patchJson($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('PATCH', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}

	/**
	 * Visit the given URI with a PUT request, expecting a JSON response.
	 *
	 * @param Uri|string $uri
	 * @param array      $data
	 * @param array      $headers
	 * @param int        $options
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function putJson($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('PUT', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}

	/**
	 * Visit the given URI with a DELETE request, expecting a JSON response.
	 *
	 * @param Uri|string $uri
	 * @param array      $data
	 * @param array      $headers
	 * @param int        $options
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	public function deleteJson($uri, array $data = [], array $headers = [], $options = 0)
	{
		return $this->json('DELETE', self::API_PREFIX . ltrim($uri, '/'), $data, $headers, $options);
	}
}
