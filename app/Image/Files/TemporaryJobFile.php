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
	 * @param string $file_path      the path of a Processable Job file
	 * @param string $fake_base_name the fake base name of the file; e.g. the original name prior to up-/download
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $file_path, string $fake_base_name = '')
	{
		$last_exception = null;
		$retry_counter = 5;
		do {
			try {
				$temp_file_path = $file_path;
				$retry_counter--;
				// We open wih c+b because the file already exists (from ProcessableJobFile)
				$this->stream = fopen($temp_file_path, 'c+b');
			} catch (\ErrorException|\Exception $e) {
				$temp_file_path = null;
				$last_exception = $e;
			}
		} while ($temp_file_path === null && $retry_counter > 0);
		if ($temp_file_path === null) {
			throw new MediaFileOperationException('unable to create temporary file', $last_exception);
		}
		parent::__construct($temp_file_path);
		$this->fakeBaseName = $fake_base_name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalBasename(): string
	{
		return $this->fakeBaseName;
	}
}