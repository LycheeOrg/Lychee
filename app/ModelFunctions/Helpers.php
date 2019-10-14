<?php

namespace App\ModelFunctions;

use App\Configs;
use Exception;

class Helpers
{
	/**
	 * Generate an id from current microtime.
	 *
	 * @return string generated ID
	 */
	public static function generateID()
	{
		// Generate id based on the current microtime

		if (PHP_INT_MAX == 2147483647
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
		if (file_exists($path) === true && is_readable($path) === true
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
		if (file_exists($path) === true && is_readable($path) === true
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
	 * @throws Exception
	 */
	public static function gcd($a, $b)
	{
		if ($b == 0) {
			throw new Exception('gcd: Modulo by zero error.');
		}

		return ($a % $b) ? Helpers::gcd($b, $a % $b) : $b;
	}
}
