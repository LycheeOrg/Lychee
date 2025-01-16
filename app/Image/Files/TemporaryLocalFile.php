<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;

/**
 * Class TemporaryLocalFile.
 *
 * Represents a local file with an automatically chosen, unique name intended
 * to be used temporarily.
 */
class TemporaryLocalFile extends NativeLocalFile
{
	use LoadTemporaryFileTrait;

	protected string $fakeBaseName;

	/**
	 * @throws MediaFileOperationException
	 */
	public function __destruct()
	{
		$this->delete();
		parent::__destruct();
	}

	/**
	 * Creates a new temporary file with a random file name.
	 *
	 * @param string $fileExtension the file extension of the new temporary file incl. a preceding dot
	 * @param string $fakeBaseName  the fake base name of the file; e.g. the original name prior to up-/download
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $fileExtension, string $fakeBaseName = '')
	{
		$tempFilePath = $this->load($fileExtension);
		parent::__construct($tempFilePath);
		$this->fakeBaseName = $fakeBaseName;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getFileBasePath(): string
	{
		return sys_get_temp_dir();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalBasename(): string
	{
		return $this->fakeBaseName;
	}
}
