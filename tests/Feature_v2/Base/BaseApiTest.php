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

use App\Enum\DownloadVariantType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Uri;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSE;

abstract class BaseApiTest extends AbstractTestCase
{
	use DatabaseTransactions;
	use RequireSE;

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
	 * @param string      $uri
	 * @param string|null $filename
	 * @param array|null  $data
	 * @param array|null  $headers
	 *
	 * @return TestResponse
	 */
	public function upload(
		string $uri,
		?string $filename = null,
		?string $album_id = null,
		int $file_last_modified_time = 1678824303000,
		?array $data = null,
		?array $headers = null,
		?string $file_name = null,
	): TestResponse {
		$data ??= [
			'album_id' => $album_id,
			'file' => static::createUploadedFile($filename),
			'file_last_modified_time' => $file_last_modified_time,
			'file_name' => $file_name ?? $filename,
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

	/**
	 * Try to download a zip file with the given parameters.
	 *
	 * @param array               $photo_ids          list of photos to download
	 * @param array               $album_ids          list of albums to download
	 * @param DownloadVariantType $kind               in the case of photo ids we can also specify the kind
	 * @param int                 $expectedStatusCode expected status code of the response (hopefully 200)
	 *
	 * @return TestResponse<JsonResponse>
	 */
	public function download(
		array $photo_ids = [],
		array $album_ids = [],
		DownloadVariantType $kind = DownloadVariantType::ORIGINAL,
		$expectedStatusCode = 200,
	): TestResponse {
		$params = [];
		if ($photo_ids !== []) {
			$params['photo_ids'] = implode(',', $photo_ids);
			$params['variant'] = $kind->value;
		}
		if ($album_ids !== []) {
			$params['album_ids'] = implode(',', $album_ids);
		}

		$response = $this->getWithParameters(self::API_PREFIX . 'Zip', $params, [
			'Accept' => '*/*',
		]
		);

		$this->assertStatus($response, $expectedStatusCode);
		if ($response->baseResponse instanceof StreamedResponse) {
			// The content of a streamed response is not generated unless
			// the content is fetched.
			// This ensures that the generator of SUT is actually executed.
			$response->streamedContent();
		}

		return $response;
	}
}
