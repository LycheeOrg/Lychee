<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use Illuminate\Http\UploadedFile as LaravelUploadedFile;

/**
 * Class `UploadedFile` wraps a {@link LaravelUploadedFile} into a unified interface.
 *
 * It provides the client-side MIME type in case the MIME type cannot be
 * inferred from the temporary, local copy of the file.
 */
class UploadedFile extends NativeLocalFile
{
	protected LaravelUploadedFile $baseFile;

	/**
	 * @throws MediaFileOperationException
	 */
	public function __construct(LaravelUploadedFile $file)
	{
		$this->baseFile = $file;
		$path = $file->getRealPath();
		if ($path === false) {
			throw new MediaFileOperationException('The uploaded file does not exist');
		}

		parent::__construct($path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalExtension(): string
	{
		return '.' . pathinfo($this->baseFile->getClientOriginalName(), PATHINFO_EXTENSION);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOriginalBasename(): string
	{
		return pathinfo($this->baseFile->getClientOriginalName(), PATHINFO_FILENAME);
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
			return $this->baseFile->getClientMimeType();
		}

		return $this->cachedMimeType;
	}
}
