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
	 */
	abstract protected function getFileBasePath(): string;

	/**
	 * Prepare a temporary file to be loaded.
	 * Name is randomly generated and will be placed in getFileBasePath() directory.
	 *
	 * @throws MediaFileOperationException
	 */
	protected function load(string $file_extension): string
	{
		// We must not use the usual PHP method `tempnam`, because that
		// method does not handle file extensions well, but our temporary
		// files need a proper (and correct) extension for the MIME extractor
		// to work.
		$last_exception = null;
		$retry_counter = 5;
		do {
			try {
				$retry_counter--;
				$temp_file_path = $this->getFileBasePath() .
					DIRECTORY_SEPARATOR .
					strtr(base64_encode(random_bytes(12)), '+/', '-_') .
					$file_extension;
				$this->stream = fopen($temp_file_path, 'x+b');
			} catch (\ErrorException|\Exception $e) {
				$temp_file_path = null;
				$last_exception = $e;
			}
		} while ($temp_file_path === null && $retry_counter > 0);
		if ($temp_file_path === null) {
			throw new MediaFileOperationException('unable to create temporary file', $last_exception);
		}

		return $temp_file_path;
	}
}