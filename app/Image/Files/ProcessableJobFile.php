<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use Illuminate\Support\Facades\Storage;
use function Safe\mkdir;

/**
 * Class TemporaryJobFile.
 *
 * Represents a local file with an automatically chosen, unique name intended
 * to be used temporarily before being processed in a Job.
 */
class ProcessableJobFile extends NativeLocalFile
{
	use LoadTemporaryFileTrait;
	public const DISK_NAME = 'image-jobs';

	protected string $fakeBaseName;

	/**
	 * Creates a new temporary file with a random file name.
	 * Do note that we MUST use storage_path() instead of sys_get_temp_dir() as
	 * tmp is not shared across processes, meaning that the queues will not be able to see the files.
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
		$tempDirPath = Storage::disk(self::DISK_NAME)->path('');

		if (!file_exists($tempDirPath)) {
			mkdir($tempDirPath);
		}

		return $tempDirPath;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalBasename(): string
	{
		return $this->fakeBaseName;
	}
}
