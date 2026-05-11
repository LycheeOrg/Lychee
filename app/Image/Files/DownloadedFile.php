<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Image\Files;

use App\Assets\Features;
use App\DTO\UrlValidatedDTO;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use function Safe\curl_exec;
use function Safe\curl_init;
use Safe\Exceptions\CurlException;
use Safe\Exceptions\PcreException;
use Safe\Exceptions\UrlException;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\mime_content_type;
use function Safe\parse_url;
use function Safe\preg_match;
use function Safe\rewind;
use function Safe\stream_copy_to_stream;
use function Safe\tmpfile;

/**
 * Represents a temporary local file which has been downloaded.
 *
 * It provides the server-side MIME type in case the MIME type cannot be
 * inferred from the temporary, local copy of the file.
 */
final class DownloadedFile extends TemporaryLocalFile
{
	protected ?string $originalMimeType = null;

	/**
	 * @throws MediaFileOperationException
	 *
	 * @phpstan-ignore constructor.missingParentCall
	 */
	public function __construct(
		UrlValidatedDTO $url,
	) {
		Features::when(
			'use_fopen_for_url_imports',
			fn () => $this->downloadWithFopen($url->url),
			fn () => $this->downloadWithCurl($url));
	}

	/**
	 * Returns the MIME type of the file.
	 *
	 * @param bool $fallback_to_client_mime_type flag to use the provided MIME
	 *                                           type by client-side, if the
	 *                                           internal PHP mechanism detects
	 *                                           "application/octet-stream"
	 *
	 * @return string the MIME type
	 *
	 * @throws MediaFileOperationException
	 */
	public function getMimeType(bool $fallback_to_client_mime_type = true): string
	{
		parent::getMimeType();
		if ($this->cachedMimeType === 'application/octet-stream' && $fallback_to_client_mime_type) {
			return $this->originalMimeType ?? throw new LycheeAssertionError('The MIME type of the downloaded file could not be determined.');
		}

		return $this->cachedMimeType;
	}

	/**
	 * Download with fopen.
	 *
	 * @param string $url
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 */
	private function downloadWithFopen(string $url): void
	{
		try {
			/** @var string $path because we provide directly PHP_URL_PATH */
			$path = parse_url($url, PHP_URL_PATH);
			$basename = pathinfo($path, PATHINFO_FILENAME);
			$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

			$config_manager = resolve(ConfigManager::class);
			$opts = [
				'http' => [
					'follow_location' => !$config_manager->getValueAsBool('import_via_url_block_redirect'),
					'max_redirects' => 3,
					'timeout' => 10.0,
				],
			];

			$context = stream_context_create($opts);
			$download_stream = fopen($url, 'rb', context: $context);
			$download_stream_data = stream_get_meta_data($download_stream);

			/** @var string|null $original_mime_type */
			$original_mime_type = null;
			// Find the server-side MIME type; the HTTP headers are part of
			// the protocol-specific meta-data of the stream handler
			foreach ($download_stream_data['wrapper_data'] as $http_header) {
				$original_mime_type = $this->extractMimeTypeFromHeader($http_header) ?? $original_mime_type;
				if ($original_mime_type !== null) {
					break;
				}
			}

			try {
				$this->writeDownloadedStream($download_stream, $basename, $extension, $original_mime_type);
			} finally {
				fclose($download_stream);
			}
			// @codeCoverageIgnoreStart
		} catch (\ErrorException|PcreException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 */
	private function downloadWithCurl(UrlValidatedDTO $url): void
	{
		try {
			/** @var string $host because the URL has already been validated */
			$host = parse_url($url->url, PHP_URL_HOST);
			/** @var string $scheme because the URL has already been validated */
			$scheme = parse_url($url->url, PHP_URL_SCHEME);
			$port = parse_url($url->url, PHP_URL_PORT) ?? ($scheme === 'https' ? 443 : 80);

			/** @var string $path because the URL has already been validated */
			$path = parse_url($url->url, PHP_URL_PATH);
			$basename = pathinfo($path, PATHINFO_FILENAME);
			$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

			$config_manager = resolve(ConfigManager::class);
			$temp = tmpfile();
			$original_mime_type = null;

			$ch = curl_init($url->url);
			if ($ch === false) {
				fclose($temp);
				throw new MediaFileOperationException('Could not initialize cURL.');
			}

			try {
				curl_setopt_array($ch, [
					CURLOPT_RESOLVE => [$host . ':' . $port . ':' . $url->resolved_ip],
					CURLOPT_RETURNTRANSFER => false,
					CURLOPT_TIMEOUT => 10,
					CURLOPT_FOLLOWLOCATION => !$config_manager->getValueAsBool('import_via_url_block_redirect'),
					CURLOPT_MAXREDIRS => 3,
					CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
					CURLOPT_FILE => $temp,
					CURLOPT_HEADERFUNCTION => function ($_curl_handle, string $http_header) use (&$original_mime_type): int {
						$original_mime_type = $this->extractMimeTypeFromHeader($http_header) ?? $original_mime_type;

						return strlen($http_header);
					},
				]);

				if (curl_exec($ch) === false) {
					$error_message = curl_error($ch);
					throw new MediaFileOperationException($error_message !== '' ? $error_message : 'Could not download file.');
				}

				rewind($temp);
				$this->writeDownloadedStream($temp, $basename, $extension, $original_mime_type);
			} finally {
				fclose($temp);
			}
		} catch (PcreException|CurlException|UrlException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * @param resource $download_stream
	 *
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 */
	private function writeDownloadedStream($download_stream, string $basename, string $extension, ?string $original_mime_type): void
	{
		// When the URL doesn't contain the file's extension, the web server may or may have not set the
		// Content-Type correctly. If the Content-Type header has a value that we recognize, we consider it valid.
		// In all other cases we try to guess the file type.
		// File extension > Content-Type > Inferred MIME type
		$file_extension_service = resolve(FileExtensionService::class);
		if ($extension !== '.' && $file_extension_service->isSupportedOrAcceptedFileExtension($extension)) {
			parent::__construct($extension, $basename); // @phpstan-ignore constructor.call
			$this->originalMimeType = $original_mime_type;
			$this->write($download_stream);

			return;
		}
		if ($original_mime_type !== null && $file_extension_service->isSupportedMimeType($original_mime_type)) {
			$extension = $file_extension_service->getDefaultFileExtensionForMimeType($original_mime_type);
			parent::__construct($extension, $basename); // @phpstan-ignore constructor.call
			$this->originalMimeType = $original_mime_type;
			$this->write($download_stream);

			return;
		}

		$temp = tmpfile();
		try {
			stream_copy_to_stream($download_stream, $temp);
			rewind($temp);
			$detected_mime_type = mime_content_type($temp);

			if ($file_extension_service->isSupportedMimeType($detected_mime_type)) {
				$extension = $file_extension_service->getDefaultFileExtensionForMimeType($detected_mime_type);
				parent::__construct($extension, $basename); // @phpstan-ignore constructor.call
				$this->originalMimeType = $detected_mime_type;
				rewind($temp);
				$this->write($temp);

				return;
			}

			throw new MediaFileUnsupportedException(MediaFileUnsupportedException::DEFAULT_MESSAGE . ' (bad file type: ' . $detected_mime_type . ')');
		} finally {
			fclose($temp);
		}
	}

	/**
	 * @throws PcreException
	 */
	private function extractMimeTypeFromHeader(string $http_header): ?string
	{
		$matches = [];
		preg_match(
			'#^Content-Type: ([-a-z]+/[-a-z]+)#i',
			$http_header,
			$matches,
			PREG_UNMATCHED_AS_NULL
		);

		return count($matches) === 2 && !is_null($matches[1]) ? $matches[1] : null;
	}
}