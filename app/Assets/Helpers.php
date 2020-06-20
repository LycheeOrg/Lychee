<?php

namespace App\Assets;

use Cache;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
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
			// @codeCoverageIgnoreStart
			$unixTimeStamp = File::lastModified($filePath);

			return "{$filePath}?{$unixTimeStamp}";
			// @codeCoverageIgnoreEnd
		}

		return $filePath;
	}

	/**
	 * checks if client is a TV
	 *
	 * @return bool
	 */
	public static function isTV(): bool
	{
		// Determine type of browser
		DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
		$userAgent = $_SERVER['HTTP_USER_AGENT'];

		if (!empty($userAgent)) {
			$dd = new DeviceDetector($userAgent);

			// Use cache since lib uses quite some regex
			// TODO -> not yet working
			//$psr6Cache = new app('cache.store');
			//$dd->setCache( new \DeviceDetector\Cache\PSR6Bridge($psr6Cache));

			// Bot detection will completely be skipped (bots will be detected as regular devices then)
			$dd->skipBotDetection();

			// Parse the user agent
			$dd->parse();


			return $dd->isTV();
		}
		return false;
	}

}
