<?php

declare(strict_types=1);

namespace App\Assets;

use App\Exceptions\Internal\ZeroModuloException;
use Illuminate\Support\Facades\File;
use function Safe\ini_get;
use function Safe\parse_url;

class Helpers
{
	/**
	 * Add UnixTimeStamp to file path suffix.
	 *
	 * @param string $filePath
	 *
	 * @return string
	 */
	public function cacheBusting(string $filePath): string
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
	 * Return the 32bit truncated version of a number seen as string.
	 *
	 * @param string $id
	 * @param int    $prevShortId
	 * @param int    $phpMax      predefined so set to MAX php during migration
	 *                            but allow to actually test the code
	 *
	 * @return string updated ID
	 */
	public function trancateIf32(string $id, int $prevShortId = 0, int $phpMax = PHP_INT_MAX): string
	{
		if ($phpMax > 2147483647) {
			return $id;
		}

		// Chop off the last four digits.
		$shortId = intval(substr($id, 0, -4));
		if ($shortId <= $prevShortId) {
			$shortId = $prevShortId + 1;
		}

		return (string) $shortId;
	}

	/**
	 * Returns the extension of the filename (path or URI) or an empty string.
	 *
	 * @param string $filename
	 * @param bool   $isURI
	 *
	 * @return string extension of the filename starting with a dot
	 */
	public function getExtension(string $filename, bool $isURI = false): string
	{
		// If $filename is an URI, get only the path component
		if ($isURI === true) {
			/** @var string $filename this is true because PHP_URL_PATH is specified */
			$filename = parse_url($filename, PHP_URL_PATH);
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// Special cases
		// https://github.com/electerious/Lychee/issues/482
		list($extension) = explode(':', $extension, 2);

		if ($extension !== '') {
			$extension = '.' . $extension;
		}

		return $extension;
	}

	/**
	 * Check if $path has readable and writable permissions.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function hasPermissions(string $path): bool
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		return
			file_exists($path) &&
			is_readable($path) &&
			is_writeable($path);
	}

	/**
	 * Check if $path has readable and writable permissions.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function hasFullPermissions(string $path): bool
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		if (
			file_exists($path) === true &&
			is_readable($path) === true &&
			is_executable($path) === true &&
			is_writeable($path) === true
		) {
			return true;
		}

		return false;
	}

	/**
	 * Compute the GCD of a and b
	 * This function is used to simplify the shutter speed when given in the form of e.g. 50/100.
	 *
	 * @param int $a
	 * @param int $b
	 *
	 * @return int
	 *
	 * @throws ZeroModuloException
	 */
	public function gcd(int $a, int $b): int
	{
		if ($b === 0) {
			throw new ZeroModuloException();
		}

		return ($a % $b) !== 0 ? $this->gcd($b, $a % $b) : $b;
	}

	/**
	 * From https://www.php.net/manual/en/function.disk-total-space.php.
	 *
	 * @param float $bytes
	 *
	 * @return string
	 */
	public function getSymbolByQuantity(float $bytes): string
	{
		$symbols = [
			'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB',
		];
		$exp = intval(floor(log($bytes) / log(1024.0)));

		if ($exp >= sizeof($symbols)) {
			// if the number is too large, we fall back to the largest available symbol
			$exp = sizeof($symbols) - 1;
		}

		return sprintf('%.2f %s', ($bytes / pow(1024, $exp)), $symbols[$exp]);
	}

	/**
	 * Check if the `exec` function is available.
	 *
	 * @return bool
	 */
	public function isExecAvailable(): bool
	{
		$disabledFunctions = explode(',', ini_get('disable_functions'));

		return function_exists('exec') && !in_array('exec', $disabledFunctions, true);
	}

	/**
	 * Given a duration convert it into hms.
	 *
	 * @param int|float $d length in seconds
	 *
	 * @return string equivalent time string formatted
	 */
	public function secondsToHMS(int|float $d): string
	{
		$h = (int) floor($d / 3600);
		$m = (int) floor(($d % 3600) / 60);
		$s = (int) floor($d % 60);

		return ($h > 0 ? $h . 'h' : '')
			. ($m > 0 ? $m . 'm' : '')
			. ($s > 0 || ($h === 0 && $m === 0) ? $s . 's' : '');
	}

	/**
	 * Return true if the upload_max_filesize is bellow what we want.
	 */
	public function convertSize(string $size): int
	{
		$size = trim($size);
		$last = strtolower($size[strlen($size) - 1]);
		$size = intval($size);

		switch ($last) {
			case 'g':
				$size *= 1024;
				// no break
			case 'm':
				$size *= 1024;
				// no break
			case 'k':
				$size *= 1024;
		}

		return $size;
	}

	/**
	 * Converts a decimal degree into integer degree, minutes and seconds.
	 *
	 * @param float|null $decimal
	 * @param bool       $type    - indicates if the passed decimal indicates a
	 *                            latitude (`true`) or a longitude (`false`)
	 *
	 * @returns string
	 */
	public function decimalToDegreeMinutesSeconds(float|null $decimal, bool $type): string|null
	{
		if ($decimal === null) {
			return null;
		}

		$d = abs($decimal);

		// absolute value of decimal must be smaller than 180;
		if ($d > 180) {
			return '';
		}

		// set direction; north assumed
		if ($type && $decimal < 0) {
			$direction = 'S';
		} elseif (!$type && $decimal < 0) {
			$direction = 'W';
		} elseif (!$type) {
			$direction = 'E';
		} else {
			$direction = 'N';
		}

		// get degrees
		$degrees = floor($d);

		// get seconds
		$seconds = ($d - $degrees) * 3600;

		// get minutes
		$minutes = floor($seconds / 60);

		// reset seconds
		$seconds = floor($seconds - $minutes * 60);

		return $degrees . '° ' . $minutes . "' " . $seconds . '" ' . $direction;
	}

	/**
	 * Censor a word by replacing half of its character by stars.
	 *
	 * @param string $string         to censor
	 * @param float  $percentOfClear the amount of the original string that remains untouched. The lower the value, the higher the censoring.
	 *
	 * @return string
	 */
	public function censor(string $string, float $percentOfClear = 0.5): string
	{
		$strLength = strlen($string);
		if ($strLength === 0) {
			return '';
		}

		// Length of replacement
		$censored_length = $strLength - (int) floor($strLength * $percentOfClear);

		// we leave half the space in front and behind.
		$start = (int) floor(($strLength - $censored_length) / 2);

		$replacement = str_repeat('*', $censored_length);

		return substr_replace($string, $replacement, $start, $censored_length);
	}
}
