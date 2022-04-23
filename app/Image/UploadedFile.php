<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;
use Illuminate\Http\UploadedFile as LaravelUploadedFile;

/**
 * Class UploadedFile.
 *
 * Wraps a {@link LaravelUploadedFile} into a unified interface.
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

	public function getOriginalExtension(): string
	{
		return pathinfo($this->baseFile->getClientOriginalName(), PATHINFO_EXTENSION);
	}

	public function getOriginalBasename(): string
	{
		return pathinfo($this->baseFile->getClientOriginalName(), PATHINFO_FILENAME);
	}
}
