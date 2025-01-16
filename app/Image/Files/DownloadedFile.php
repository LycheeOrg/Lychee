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
	 * @param string $url
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $url)
	{
		try {
			/** @var string $path because we provide directly PHP_URL_PATH */
			$path = parse_url($url, PHP_URL_PATH);
			$basename = pathinfo($path, PATHINFO_FILENAME);
			$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

			$downloadStream = fopen($url, 'rb');
			$downloadStreamData = stream_get_meta_data($downloadStream);

			/** @var string|null $originalMimeType */
			$originalMimeType = null;
			// Find the server-side MIME type; the HTTP headers are part of
			// the protocol-specific meta-data of the stream handler
			foreach ($downloadStreamData['wrapper_data'] as $http_header) {
				$matches = [];
				preg_match(
					'#^Content-Type: ([-a-z]+/[-a-z]+)#i',
					$http_header,
					$matches,
					PREG_UNMATCHED_AS_NULL
				);
				if (count($matches) === 2 && $matches[1]) {
					$originalMimeType = $matches[1];
					break;
				}
			}

			// When the URL doesn't contain the file's extension, the web server may or may have not set the
			// Content-Type correctly. If the Content-Type header has a value that we recognize, we consider it valid.
			// In all other cases we try to guess the file type.
			// File extension > Content-Type > Inferred MIME type

			if (self::isSupportedOrAcceptedFileExtension($extension)) {
				parent::__construct($extension, $basename);
				$this->originalMimeType = $originalMimeType;
				$this->write($downloadStream);
				fclose($downloadStream);

				return;
			}

			if (self::isSupportedMimeType($originalMimeType)) {
				$extension = self::getDefaultFileExtensionForMimeType($originalMimeType);
				parent::__construct($extension, $basename);
				$this->originalMimeType = $originalMimeType;
				$this->write($downloadStream);
				fclose($downloadStream);

				return;
			}

			$temp = tmpfile();
			stream_copy_to_stream($downloadStream, $temp);
			fclose($downloadStream);

			rewind($temp);
			$originalMimeType = mime_content_type($temp);

			if (self::isSupportedMimeType($originalMimeType)) {
				$extension = self::getDefaultFileExtensionForMimeType($originalMimeType);
				parent::__construct($extension, $basename);
				$this->originalMimeType = $originalMimeType;
				rewind($temp);
				$this->write($temp);
				fclose($temp);

				return;
			}

			fclose($temp);
			throw new MediaFileUnsupportedException(MediaFileUnsupportedException::DEFAULT_MESSAGE . ' (bad file type: ' . $originalMimeType . ')');
		} catch (\ErrorException|PcreException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Returns the MIME type of the file.
	 *
	 * @param bool $fallbackToClientMimeType flag to use the provided MIME
	 *                                       type by client-side, if the
	 *                                       internal PHP mechanism detects
	 *                                       "application/octet-stream"
	 *
	 * @return string the MIME type
	 *
	 * @throws MediaFileOperationException
	 */
	public function getMimeType(bool $fallbackToClientMimeType = true): string
	{
		parent::getMimeType();
		if ($this->cachedMimeType === 'application/octet-stream' && $fallbackToClientMimeType) {
			return $this->originalMimeType;
		} else {
			return $this->cachedMimeType;
		}
	}
}
