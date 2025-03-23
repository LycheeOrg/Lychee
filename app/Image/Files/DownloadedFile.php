<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use Safe\Exceptions\PcreException;
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
class DownloadedFile extends TemporaryLocalFile
{
	protected ?string $originalMimeType = null;

	/**
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $url)
	{
		try {
			/** @var string $path because we provide directly PHP_URL_PATH */
			$path = parse_url($url, PHP_URL_PATH);
			$basename = pathinfo($path, PATHINFO_FILENAME);
			$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

			$download_stream = fopen($url, 'rb');
			$download_stream_data = stream_get_meta_data($download_stream);

			/** @var string|null $original_mime_type */
			$original_mime_type = null;
			// Find the server-side MIME type; the HTTP headers are part of
			// the protocol-specific meta-data of the stream handler
			foreach ($download_stream_data['wrapper_data'] as $http_header) {
				$matches = [];
				preg_match(
					'#^Content-Type: ([-a-z]+/[-a-z]+)#i',
					$http_header,
					$matches,
					PREG_UNMATCHED_AS_NULL
				);
				if (count($matches) === 2 && !is_null($matches[1])) {
					$original_mime_type = $matches[1];
					break;
				}
			}

			// When the URL doesn't contain the file's extension, the web server may or may have not set the
			// Content-Type correctly. If the Content-Type header has a value that we recognize, we consider it valid.
			// In all other cases we try to guess the file type.
			// File extension > Content-Type > Inferred MIME type

			if (self::isSupportedOrAcceptedFileExtension($extension)) {
				parent::__construct($extension, $basename);
				$this->originalMimeType = $original_mime_type;
				$this->write($download_stream);
				fclose($download_stream);

				return;
			}

			if (self::isSupportedMimeType($original_mime_type)) {
				$extension = self::getDefaultFileExtensionForMimeType($original_mime_type);
				parent::__construct($extension, $basename);
				$this->originalMimeType = $original_mime_type;
				$this->write($download_stream);
				fclose($download_stream);

				return;
			}

			$temp = tmpfile();
			stream_copy_to_stream($download_stream, $temp);
			fclose($download_stream);

			rewind($temp);
			$original_mime_type = mime_content_type($temp);

			if (self::isSupportedMimeType($original_mime_type)) {
				$extension = self::getDefaultFileExtensionForMimeType($original_mime_type);
				parent::__construct($extension, $basename);
				$this->originalMimeType = $original_mime_type;
				rewind($temp);
				$this->write($temp);
				fclose($temp);

				return;
			}

			fclose($temp);
			throw new MediaFileUnsupportedException(MediaFileUnsupportedException::DEFAULT_MESSAGE . ' (bad file type: ' . $original_mime_type . ')');
		} catch (\ErrorException|PcreException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
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
			return $this->originalMimeType;
		} else {
			return $this->cachedMimeType;
		}
	}
}