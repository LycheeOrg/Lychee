<?php

namespace App\Assets;

use App\Configs;
use App\Exceptions\DivideByZeroException;
use Illuminate\Support\Facades\File;
use WhichBrowser\Parser as BrowserParser;

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
	 * return device type as string:
	 * desktop, mobile, pda, dect, tablet, gaming, ereader,
	 * media, headset, watch, emulator, television, monitor,
	 * camera, printer, signage, whiteboard, devboard, inflight,
	 * appliance, gps, car, pos, bot, projector.
	 *
	 * @return string
	 */
	public static function getDeviceType(): string
	{
		$result = new BrowserParser(getallheaders(), ['cache' => app('cache.store')]);

		return $result->getType();
	}

	/*
	 * Generate an id from current microtime.
	 *
	 * @return string generated ID
	 */
	public static function generateID()
	{
		// Generate id based on the current microtime

		if (
			PHP_INT_MAX == 2147483647
			|| Configs::get_value('force_32bit_ids', '0') === '1'
		) {
			// For 32-bit installations, we can only afford to store the
			// full seconds in id.  The calling code needs to be able to
			// handle duplicate ids.  Note that this also exposes us to
			// the year 2038 problem.
			$id = sprintf('%010d', microtime(true));
		} else {
			// Ensure 4 digits after the decimal point, 15 characters
			// total (including the decimal point), 0-padded on the
			// left if needed (shouldn't be needed unless we move back in
			// time :-) )
			$id = sprintf('%015.4f', microtime(true));
			$id = str_replace('.', '', $id);
		}

		return $id;
	}

	/**
	 * Return the 32bit truncated version of a number seen as string.
	 *
	 * @param string $id
	 * @param int    $prevShortId
	 *
	 * @return string updated ID
	 */
	public static function trancateIf32(string $id, int $prevShortId = 0)
	{
		if (PHP_INT_MAX > 2147483647) {
			return $id;
		}

		// Chop off the last four digits.
		$shortId = intval(substr($id, 0, -4));
		if ($shortId <= $prevShortId) {
			$shortId = $prevShortId + 1;
		}

		return $shortId;
	}

	/**
	 * Returns the extension of the filename (path or URI) or an empty string.
	 *
	 * @param $filename
	 * @param bool $isURI
	 *
	 * @return string extension of the filename starting with a dot
	 */
	public static function getExtension($filename, $isURI = false)
	{
		// If $filename is an URI, get only the path component
		if ($isURI === true) {
			$filename = parse_url($filename, PHP_URL_PATH);
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// Special cases
		// https://github.com/electerious/Lychee/issues/482
		list($extension) = explode(':', $extension, 2);

		if (empty($extension) === false) {
			$extension = '.' . $extension;
		}

		return $extension;
	}

	/**
	 * Check if $path has readable and writable permissions.
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public static function hasPermissions($path)
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		if (
			file_exists($path) === true && is_readable($path) === true
			&& is_writeable($path) === true
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if $path has readable and writable permissions.
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public static function hasFullPermissions($path)
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		if (
			file_exists($path) === true && is_readable($path) === true
			&& is_executable($path) === true
			&& is_writeable($path) === true
		) {
			return true;
		}

		return false;
	}

	/**
	 * Compute the GCD of a and b
	 * This function is used to simplify the shutter speed when given in the form of e.g. 50/100.
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 *
	 * @throws DivideByZeroException
	 */
	public static function gcd($a, $b)
	{
		if ($b == 0) {
			throw new DivideByZeroException();
		}

		return ($a % $b) ? Helpers::gcd($b, $a % $b) : $b;
	}

	/**
	 * Properly convert a boolean to a string
	 * the default php function returns '' in case of false, this is not the behavior we want.
	 */
	public static function str_of_bool(bool $b)
	{
		return $b ? '1' : '0';
	}

	/**
	 * Given a Url generate the @2x correcponding url.
	 * This is used for thumbs, small and medium.
	 */
	public static function ex2x($url)
	{
		$thumbUrl2x = explode('.', $url);

		return $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
	}
}
