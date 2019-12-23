<?php

namespace App\Assets;

use Illuminate\Support\Facades\File;

class Helpers
{
	/**
	 * Add UnixTimeStamp to file path suffix.
	 *
	 * @param string $filePath
	 *
	 * @return string
	 */
	public static function cacheBusting(string $filePath): string
	{
		if (File::exists($filePath)) {
			$unixTimeStamp = File::lastModified($filePath);

			return "{$filePath}?{$unixTimeStamp}";
		}

		return $filePath;
	}
}

