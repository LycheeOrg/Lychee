<?php

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use function Safe\fopen;

/**
 * Class TemporaryLocalFile.
 *
 * Represents a local file with an automatically chosen, unique name intended
 * to be used temporarily.
 */
class TemporaryLocalFile extends NativeLocalFile
{
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
		// We must not use the usual PHP method `tempnam`, because that
		// method does not handle file extensions well, but our temporary
		// files need a proper (and correct) extension for the MIME extractor
		// to work.
		$lastException = null;
		$retryCounter = 5;
		do {
			try {
				$tempFilePath = sys_get_temp_dir() .
					DIRECTORY_SEPARATOR .
					'lychee-' .
					strtr(base64_encode(random_bytes(12)), '+/', '-_') .
					$fileExtension;
				$retryCounter--;
				$this->stream = fopen($tempFilePath, 'x+b');
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
