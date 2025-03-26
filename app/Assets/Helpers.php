<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Assets;

use App\Exceptions\Internal\ZeroModuloException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use function Safe\ini_get;

class Helpers
{
	/**
	 * Add UnixTimeStamp to file path suffix.
	 */
	public function cacheBusting(string $file_path): string
	{
		if (File::exists($file_path)) {
			// @codeCoverageIgnoreStart
			$unix_time_stamp = File::lastModified($file_path);

			return "{$file_path}?{$unix_time_stamp}";
			// @codeCoverageIgnoreEnd
		}

		return $file_path;
	}

	/**
	 * Return the 32bit truncated version of a number seen as string.
	 *
	 * @param int $php_max predefined so set to MAX php during migration
	 *                     but allow to actually test the code
	 *
	 * @return string updated ID
	 */
	public function trancateIf32(string $id, int $prev_short_id = 0, int $php_max = PHP_INT_MAX): string
	{
		if ($php_max > 2147483647) {
			return $id;
		}

		// Chop off the last four digits.
		$short_id = intval(substr($id, 0, -4));
		if ($short_id <= $prev_short_id) {
			$short_id = $prev_short_id + 1;
		}

		return (string) $short_id;
	}

	/**
	 * Check if $path has readable and writable permissions.
	 */
	public function hasPermissions(string $path): bool
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		return
			file_exists($path) &&
			is_readable($path) &&
			is_writable($path);
	}

	/**
	 * Check if $path has readable and writable permissions.
	 */
	public function hasFullPermissions(string $path): bool
	{
		// Check if the given path is readable and writable
		// Both functions are also verifying that the path exists
		if (
			file_exists($path) === true &&
			is_readable($path) === true &&
			is_executable($path) === true &&
			is_writable($path) === true
		) {
			return true;
		}

		return false;
	}

	/**
	 * Compute the GCD of a and b
	 * This function is used to simplify the shutter speed when given in the form of e.g. 50/100.
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
	 */
	public function getSymbolByQuantity(float $bytes): string
	{
		$symbols = [
			'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB',
		];
		$exp = intval(floor(log($bytes) / log(1024.0)));

		if ($exp >= count($symbols)) {
			// @codeCoverageIgnoreStart
			// if the number is too large, we fall back to the largest available symbol
			$exp = count($symbols) - 1;
			// @codeCoverageIgnoreEnd
		}

		return sprintf('%.2f %s', ($bytes / pow(1024, $exp)), $symbols[$exp]);
	}

	/**
	 * Check if the `exec` function is available.
	 */
	public function isExecAvailable(): bool
	{
		$disabled_functions = explode(',', ini_get('disable_functions'));

		return function_exists('exec') && !in_array('exec', $disabled_functions, true);
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
	 * @param bool $type - indicates if the passed decimal indicates a
	 *                   latitude (`true`) or a longitude (`false`)
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
	 * @param string $string           to censor
	 * @param float  $percent_of_clear the amount of the original string that remains untouched. The lower the value, the higher the censoring.
	 */
	public function censor(string $string, float $percent_of_clear = 0.5): string
	{
		$str_length = strlen($string);
		if ($str_length === 0) {
			return '';
		}

		// Length of replacement
		$censored_length = $str_length - (int) floor($str_length * $percent_of_clear);

		// we leave half the space in front and behind.
		$start = (int) floor(($str_length - $censored_length) / 2);

		$replacement = str_repeat('*', $censored_length);

		return substr_replace($string, $replacement, $start, $censored_length);
	}

	/**
	 * Format exception trace as text.
	 *
	 * @codeCoverageIgnore
	 */
	public function exceptionTraceToText(\Exception $e): string
	{
		$renderer = new ArrayToTextTable();

		return $renderer->getTable(collect($e->getTrace())->map(fn (array $err) => [
			'class' => $err['class'] ?? $err['file'] ?? '?',
			'line' => $err['line'] ?? '?',
			'function' => $err['function']])->all());
	}

	/**
	 * Given a request return the uri WITH the query paramters.
	 * This makes sure that we handle the case where the query parameters are empty or contains an album id or pagination.
	 */
	public function getUriWithQueryString(Request $request): string
	{
		/** @var array<string,mixed>|null $query */
		$query = $request->query();
		if ($query === null || $query === []) {
			return $request->path();
		}

		return $request->path() . '?' . http_build_query($query);
	}
}