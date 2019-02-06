<?php

namespace App\ModelFunctions;

class Helpers
{
	/**
	 * @return string Generated ID.
	 */
	static public function generateID()
	{

		// Generate id based on the current microtime
		$id = str_replace(' ', '', microtime(true));
		$id = str_replace('.', '', $id);

		// Ensure that the id has a length of 10 chars
		while (strlen($id) < 14) {
			$id = '0'.$id;
		}

		// Return id as a string. Don't convert the id to an integer
		// as 14 digits are too big for 32bit PHP versions.
		return $id;

	}



	/**
	 * Returns the extension of the filename (path or URI) or an empty string.
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



	static public function hasPermissions($path)
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		if (is_readable($path) === true && is_writeable($path) === true) {
			return true;
		}
		return false;
	}



	static public function gcd($a, $b)
	{
		return ($a % $b) ? Helpers::gcd($b, $a % $b) : $b;
	}


}