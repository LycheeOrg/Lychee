<?php

namespace App\ModelFunctions;

class Helpers
{
	/**
	 * Generate an id from current microtime
	 *
	 * @return string Generated ID.
	 */
	static public function generateID()
	{

		// Generate id based on the current microtime
		// Ensure 4 digits after the decimal point, 15 characters
		// total (including the decimal point), 0-padded on the
		// left if needed (shouldn't be needed unless we move back in
		// time :-) )
		$id = sprintf("%015.4f", microtime(true));
		$id = str_replace('.', '', $id);

		// Return id as a string. Don't convert the id to an integer
		// as 14 digits are too big for 32bit PHP versions.
		return $id;

	}



	/**
	 * Returns the extension of the filename (path or URI) or an empty string.
	 *
	 * @param $filename
	 * @param bool $isURI
	 * @return string Extension of the filename starting with a dot.
	 */
	static public function getExtension($filename, $isURI = false)
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
			$extension = '.'.$extension;
		}

		return $extension;

	}



	/**
	 * Check if $path has readable and writable permissions.
	 *
	 * @param $path
	 * @return bool
	 */
	static public function hasPermissions($path)
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		if (is_readable($path) === true && is_writeable($path) === true) {
			return true;
		}
		return false;
	}



	/**
	 * Compute the GCD of a and b
	 * This function is used to simplify the shutter speed when given in the form of e.g. 50/100
	 *
	 * @param $a
	 * @param $b
	 * @return mixed
	 */
	static public function gcd($a, $b)
	{
		return ($a % $b) ? Helpers::gcd($b, $a % $b) : $b;
	}
}