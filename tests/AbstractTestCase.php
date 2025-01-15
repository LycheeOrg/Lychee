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

namespace Tests;

use App\Models\Configs;
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Testing\TestResponse;
use function Safe\copy;
use function Safe\json_decode;
use function Safe\tempnam;
use Tests\Constants\TestConstants;
use Tests\Traits\CatchFailures;

abstract class AbstractTestCase extends BaseTestCase
{
	use CreatesApplication;
	use CatchFailures;

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
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
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
	 * @param TestResponse<\Illuminate\Http\JsonResponse> $response
	 *
	 * @return object
	 */
	protected static function convertJsonToObject(TestResponse $response): object
	{
		$content = $response->getContent();
		self::assertNotFalse($content);

		return json_decode($content);
	}

	/**
	 * Creates a new "uploaded" file from one of the sample files.
	 *
	 * This method creates a copy of the sample file with a random file name
	 * and without any extension in the system's temporary directory.
	 * This mimics the exact behaviour of a true upload.
	 * In particular, the missing file extension is important as we don't
	 * have this for true uploads either, and without a file extension
	 * the MIME detector cannot rely on that.
	 *
	 * @param string $sampleFilePath the relative path to the sample file;
	 *                               use one of the `SAMPLE_FILE_...`-constants
	 *
	 * @return UploadedFile
	 */
	protected static function createUploadedFile(string $sampleFilePath): UploadedFile
	{
		$tmpFilename = tempnam(sys_get_temp_dir(), 'lychee');
		copy(base_path($sampleFilePath), $tmpFilename);

		return new UploadedFile(
			$tmpFilename,
			pathinfo($sampleFilePath, PATHINFO_BASENAME),
			TestConstants::SAMPLE_FILES_2_MIME[$sampleFilePath],
			UPLOAD_ERR_OK,
			true
		);
	}

	protected static function importPath(string $path = ''): string
	{
		return public_path(TestConstants::PATH_IMPORT_DIR . $path);
	}

	/**
	 * @return BaseCollection<int,string> the IDs of recently added photos
	 */
	protected static function getRecentPhotoIDs(): BaseCollection
	{
		$strRecent = Carbon::now()
			->subDays(Configs::getValueAsInt('recent_age'))
			->setTimezone('UTC')
			->format('Y-m-d H:i:s');
		$recentFilter = function (Builder $query) use ($strRecent) {
			$query->where('created_at', '>=', $strRecent);
		};

		return Photo::query()->select('id')->where($recentFilter)->pluck('id');
	}

	/**
	 * Because we are now using hard coded urls for the images size_variants instead of relative.
	 * We need to drop that prefix in order to access them from public_path().
	 *
	 * @param string $url
	 *
	 * @return string prefix removed
	 */
	protected function dropUrlPrefix(string $url): string
	{
		return str_replace(config('app.url'), '', $url);
	}
}
