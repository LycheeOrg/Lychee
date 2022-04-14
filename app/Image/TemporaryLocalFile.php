<?php

namespace App\Image;

/**
 * Class TemporaryLocalFile.
 *
 * Represents a local file with a automatically chosen, unique name intended
 * to be used temporarily.
 */
class TemporaryLocalFile extends NativeLocalFile
{
	/**
	 * @param string $fileExtension the file extension of the new temporary file incl. a preceding dot
	 *
	 * @throws \RuntimeException
	 */
	public function __construct(string $fileExtension)
	{
		$tempFilePath = tempnam(sys_get_temp_dir(), 'lychee') . $fileExtension;
		if ($tempFilePath === false) {
			throw new \RuntimeException('Could not create temporary file');
		}
		parent::__construct($tempFilePath);
	}
}
