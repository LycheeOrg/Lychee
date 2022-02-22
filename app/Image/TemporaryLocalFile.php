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
	public function __construct()
	{
		$tempFilePath = tempnam(sys_get_temp_dir(), 'lychee');
		if ($tempFilePath === false) {
			throw new \RuntimeException('Could not create temporary file');
		}
		parent::__construct($tempFilePath);
	}
}
