<?php

namespace App\Assets;

use App\Exceptions\Internal\ZeroModuloException;
use Illuminate\Support\Facades\File;
use function Safe\ini_get;
use function Safe\parse_url;

class Helpers
{
	private int $numTab = 0;

	/**
	 * Initialize the Facade.
	 */
	public function __construct()
	{
		$this->numTab = 0;
	}

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
	 * Returns the available licenses.
	 */
	public function get_all_licenses(): array
	{
		return [
			'none',
			'reserved',
			'CC0',
			'CC-BY-1.0',
			'CC-BY-2.0',
			'CC-BY-2.5',
			'CC-BY-3.0',
			'CC-BY-4.0',
			'CC-BY-NC-1.0',
			'CC-BY-NC-2.0',
			'CC-BY-NC-2.5',
			'CC-BY-NC-3.0',
			'CC-BY-NC-4.0',
			'CC-BY-NC-ND-1.0',
			'CC-BY-NC-ND-2.0',
			'CC-BY-NC-ND-2.5',
			'CC-BY-NC-ND-3.0',
			'CC-BY-NC-ND-4.0',
			'CC-BY-NC-SA-1.0',
			'CC-BY-NC-SA-2.0',
			'CC-BY-NC-SA-2.5',
			'CC-BY-NC-SA-3.0',
			'CC-BY-NC-SA-4.0',
			'CC-BY-ND-1.0',
			'CC-BY-ND-2.0',
			'CC-BY-ND-2.5',
			'CC-BY-ND-3.0',
			'CC-BY-ND-4.0',
			'CC-BY-SA-1.0',
			'CC-BY-SA-2.0',
			'CC-BY-SA-2.5',
			'CC-BY-SA-3.0',
			'CC-BY-SA-4.0',
		];
	}

	/**
	 * Return incrementing numbers.
	 */
	public function data_index(): int
	{
		$this->numTab++;

		return $this->numTab;
	}

	/**
	 * Reset and return incrementing numbers.
	 */
	public function data_index_r(): int
	{
		$this->numTab = 1;

		return $this->numTab;
	}

	/**
	 * Reset the incrementing number.
	 */
	public function data_index_set(int $idx = 0): void
	{
		$this->numTab = $idx;
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
}
