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
		// We must not use the usual PHP method `tempnam`, because that
		// method does not handle file extensions well, but our temporary
		// files need a proper (and correct) extension for the MIME extractor
		// to work.
		$success = false;
		$retryCounter = 5;
		do {
			$tempFilePath = sys_get_temp_dir() .
				DIRECTORY_SEPARATOR .
				'lychee-' .
				strtr(base64_encode(random_bytes(12)), '+/', '-_') .
				$fileExtension;
			try {
				$retryCounter--;
				$this->stream = fopen($tempFilePath, 'x');
				$success = is_resource($this->stream);
				fclose($this->stream);
			} catch (\Throwable) {
				$success = false;
			}
		} while (!$success && $retryCounter > 0);
		if (!$success) {
			throw new \RuntimeException('unable to create temporary file');
		}
		parent::__construct($tempFilePath);
	}
}
