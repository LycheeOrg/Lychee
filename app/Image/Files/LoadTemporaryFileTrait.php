<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use function Safe\fopen;

trait LoadTemporaryFileTrait
{
	/**
	 * This returns the base path to use to store files.
	 *
	 * @return string
	 */
	abstract protected function getFileBasePath(): string;

	/**
	 * Prepare a temporary file to be loaded.
	 * Name is randomly generated and will be placed in getFileBasePath() directory.
	 *
	 * @param string $fileExtension
	 *
	 * @return string
	 *
	 * @throws MediaFileOperationException
	 */
	protected function load(string $fileExtension): string
	{
		// We must not use the usual PHP method `tempnam`, because that
		// method does not handle file extensions well, but our temporary
		// files need a proper (and correct) extension for the MIME extractor
		// to work.
		$lastException = null;
		$retryCounter = 5;
		do {
			try {
				$retryCounter--;
				$tempFilePath = $this->getFileBasePath() .
					DIRECTORY_SEPARATOR .
					strtr(base64_encode(random_bytes(12)), '+/', '-_') .
					$fileExtension;
				$this->stream = fopen($tempFilePath, 'x+b');
			} catch (\ErrorException|\Exception $e) {
				$tempFilePath = null;
				$lastException = $e;
			}
		} while ($tempFilePath === null && $retryCounter > 0);
		if ($tempFilePath === null) {
			throw new MediaFileOperationException('unable to create temporary file', $lastException);
		}

		return $tempFilePath;
	}
}
