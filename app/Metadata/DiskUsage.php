<?php

namespace App\Metadata;

use App\Facades\Helpers;
use Illuminate\Support\Facades\Storage;
use function Safe\disk_free_space;
use function Safe\disk_total_space;
use function Safe\exec;
use function Safe\filesize;

class DiskUsage
{
	/**
	 * Returns true if the current system is windows.
	 *
	 * @return bool
	 */
	public function is_win(): bool
	{
		$os = strtoupper(substr(PHP_OS, 0, 3));

		return $os === 'WIN';
	}

	/**
	 * from https://stackoverflow.com/questions/478121/how-to-get-directory-size-in-php.
	 *
	 * @param string $dir
	 *
	 * @return int
	 */
	public function getTotalSize(string $dir): int
	{
		$dir = rtrim(str_replace('\\', '/', $dir), '/');

		if (is_dir($dir) === true) {
			// If on a Unix Host (Linux, Mac OS)
			if (!$this->is_win() && Helpers::isExecAvailable()) {
				$command = "ls -ltrR {$dir} |awk '{print $5}'|awk 'BEGIN{sum=0} {sum=sum+$1} END {print sum}' 2>&1";
				exec($command, $output);
				$size = $output[0] ?? 0;

				return intval($size);
			} // If on a Windows Host (WIN32, WINNT, Windows)
			// @codeCoverageIgnoreStart
			else {
				if (extension_loaded('com_dotnet')) {
					$obj = new \COM('scripting.filesystemobject');
					$ref = $obj->getfolder($dir);
					$totalSize = $ref->size;
					$obj = null;

					return $totalSize;
				}
			}

			return 0;
		} else {
			if (is_file($dir) === true) {
				return filesize($dir);
			}
		}

		return 0;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Return the total space available on / (we assume we run on / ).
	 *
	 * @return string
	 */
	public function get_total_space(): string
	{
		// TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$dts = disk_total_space(base_path(''));

		return Helpers::getSymbolByQuantity($dts);
	}

	/**
	 * Return the free space available on / (we assume we run on / ).
	 *
	 * @return string
	 */
	public function get_free_space(): string
	{
		// TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$dfs = disk_free_space(base_path(''));

		return Helpers::getSymbolByQuantity($dfs);
	}

	/**
	 * Return the percentage of free space available on / (we assume we run on / ).
	 *
	 * @return string
	 */
	public function get_free_percent(): string
	{
		// TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$dts = disk_total_space(base_path(''));
		$dfs = disk_free_space(base_path(''));

		return floor(100 * $dfs / $dts) . '%';
	}

	/**
	 * Return the space taken by the Lychee installation.
	 *
	 * @return string
	 */
	public function get_lychee_space(): string
	{
		$ds = $this->getTotalSize(base_path(''));

		return Helpers::getSymbolByQuantity($ds);
	}

	/**
	 * Return the space taken by the upload folder (Big, Medium, Small, Thumbs).
	 *
	 * @return string
	 */
	public function get_lychee_upload_space(): string
	{
		$ds = $this->getTotalSize(Storage::disk('images')->path(''));

		return Helpers::getSymbolByQuantity($ds);
	}
}
