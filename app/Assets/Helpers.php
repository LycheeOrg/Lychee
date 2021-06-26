<?php

namespace App\Assets;

use App\Exceptions\DivideByZeroException;
use App\Models\Logs;
use Illuminate\Support\Facades\File;
use WhichBrowser\Parser as BrowserParser;

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
	 * return device type as string:
	 * desktop, mobile, pda, dect, tablet, gaming, ereader,
	 * media, headset, watch, emulator, television, monitor,
	 * camera, printer, signage, whiteboard, devboard, inflight,
	 * appliance, gps, car, pos, bot, projector.
	 *
	 * @return string
	 */
	public function getDeviceType(): string
	{
		$result = new BrowserParser(getallheaders(), ['cache' => app('cache.store')]);

		return $result->getType();
	}

	/**
	 * Return the 32bit truncated version of a number seen as string.
	 *
	 * @param string $id
	 * @param int    $prevShortId
	 *
	 * @return string updated ID
	 */
	public function trancateIf32(string $id, int $prevShortId = 0): string
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
	 * @param string $filename
	 * @param bool   $isURI
	 *
	 * @return string extension of the filename starting with a dot
	 */
	public function getExtension(string $filename, bool $isURI = false): string
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
	 * @param string $path
	 *
	 * @return bool
	 */
	public function hasPermissions(string $path): bool
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
	 * Creates a temporary file in the local system's temporary directory with
	 * a random name and the designated extension.
	 *
	 * The caller is responsible to move/delete the temporary file after it is
	 * not needed anymore.
	 *
	 * @param string $extension the desired file extension, must include a starting dot
	 *
	 * @return string the path to the newly created file
	 */
	public function createTemporaryFile(string $extension): string
	{
		if (!($result = tempnam(sys_get_temp_dir(), 'lychee')) ||
			!rename($result, $result . $extension)) {
			$msg = 'Could not create a temporary file.';
			Logs::notice(__METHOD__, __LINE__, $msg);
			throw new \RuntimeException($msg);
		}

		return $result . $extension;
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
	 * @throws DivideByZeroException
	 */
	public function gcd(int $a, int $b): int
	{
		if ($b == 0) {
			throw new DivideByZeroException();
		}

		return ($a % $b) ? $this->gcd($b, $a % $b) : $b;
	}

	/**
	 * Properly convert a boolean to a string
	 * the default php function returns '' in case of false, this is not the behavior we want.
	 */
	public function str_of_bool(bool $b): string
	{
		return $b ? '1' : '0';
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
}
