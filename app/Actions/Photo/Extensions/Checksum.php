<?php

namespace App\Actions\Photo\Extensions;

use App\Exceptions\JsonError;
use App\Models\Logs;

trait Checksum
{
	public function checksum($filename)
	{
		// Calculate checksum
		$checksum = sha1_file($filename);
		if ($checksum === false) {
			// @codeCoverageIgnoreStart
			Logs::error(__METHOD__, __LINE__, 'Could not compute checksum for: ' . $filename);
			throw new JsonError('Could not compute checksum for photo!');
			// @codeCoverageIgnoreEnd
		}

		return $checksum;
	}
}
