<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Constants\FileSystem;
use App\Exceptions\MediaFileOperationException;
use Illuminate\Support\Facades\Storage;
use function Safe\mkdir;

/**
 * Class ProcessableJobFile.
 *
 * Represents a local file with an automatically chosen, unique name intended
 * to be used temporarily before being processed in a Job.
 */
class ProcessableJobFile extends NativeLocalFile
{
	use LoadTemporaryFileTrait;

	protected string $fake_base_name;

	/**
	 * Creates a new temporary file with a random file name.
	 * Do note that we MUST use storage_path() instead of sys_get_temp_dir() as
	 * tmp is not shared across processes, meaning that the queues will not be able to see the files.
	 *
	 * @param string $file_extension the file extension of the new temporary file incl. a preceding dot
	 * @param string $fake_base_name the fake base name of the file; e.g. the original name prior to up-/download
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $file_extension, string $fake_base_name = '')
	{
		$temp_file_path = $this->load($file_extension);
		parent::__construct($temp_file_path);
		$this->fake_base_name = $fake_base_name;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getFileBasePath(): string
	{
		$temp_dir_path = Storage::disk(FileSystem::IMAGE_JOBS)->path('');

		if (!file_exists($temp_dir_path)) {
			// @codeCoverageIgnoreStart
			mkdir($temp_dir_path);
			// @codeCoverageIgnoreEnd
		}

		return $temp_dir_path;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalBasename(): string
	{
		return $this->fake_base_name;
	}
}