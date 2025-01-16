<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use function Safe\fopen;

/**
 * Class TemporaryJobFile.
 *
 * Represents a local file with an automatically chosen, unique name intended
 * to be used temporarily.
 */
class TemporaryJobFile extends NativeLocalFile
{
	protected string $fakeBaseName;

	/**
	 * Once we are done with the process, we can delete the image.
	 *
	 * @throws MediaFileOperationException
	 */
	public function __destruct()
	{
		$this->delete();
		parent::__destruct();
	}

	/**
	 * Load a temporary file with a previously generated file name.
	 *
	 * @param string $filePath     the path of a Processable Job file
	 * @param string $fakeBaseName the fake base name of the file; e.g. the original name prior to up-/download
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $filePath, string $fakeBaseName = '')
	{
		$lastException = null;
		$retryCounter = 5;
		do {
			try {
				$tempFilePath = $filePath;
				$retryCounter--;
				// We open wih c+b because the file already exists (from ProcessableJobFile)
				$this->stream = fopen($tempFilePath, 'c+b');
			} catch (\ErrorException|\Exception $e) {
				$tempFilePath = null;
				$lastException = $e;
			}
		} while ($tempFilePath === null && $retryCounter > 0);
		if ($tempFilePath === null) {
			throw new MediaFileOperationException('unable to create temporary file', $lastException);
		}
		parent::__construct($tempFilePath);
		$this->fakeBaseName = $fakeBaseName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalBasename(): string
	{
		return $this->fakeBaseName;
	}
}
