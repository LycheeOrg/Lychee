<?php

namespace App\Metadata;

class DiskUsage
{
	/**
	 * Returns true if the current system is windows.
	 *
	 * @return bool
	 */
	public function is_win()
	{
		$os = strtoupper(substr(PHP_OS, 0, 3));

		return $os === 'WIN';
	}

	/**
	 * From https://www.php.net/manual/en/function.disk-total-space.php.
	 *
	 * @param $bytes
	 *
	 * @return string
	 */
	public function getSymbolByQuantity($bytes)
	{
		$symbols = [
			'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB',
		];
		$exp = intval(floor(log($bytes) / log(1024)));

		return sprintf('%.2f %s', ($bytes / pow(1024, $exp)),
			$symbols[$exp]);
	}

	/**
	 * from https://stackoverflow.com/questions/478121/how-to-get-directory-size-in-php.
	 *
	 * @param $dir
	 *
	 * @return bool|false|int|string
	 */
	public function getTotalSize($dir)
	{
		$dir = rtrim(str_replace('\\', '/', $dir), '/');

		if (is_dir($dir) === true) {
			// If on a Unix Host (Linux, Mac OS)
			if (!$this->is_win()) {
				$command = "ls -ltrR {$dir} |awk '{print $5}'|awk 'BEGIN{sum=0} {sum=sum+$1} END {print sum}' 2>&1";
				exec($command, $output);
				$size = $output[0] ?? 0;

				return intval($size);
			} // If on a Windows Host (WIN32, WINNT, Windows)
			// @codeCoverageIgnoreStart
			else {
				if (extension_loaded('com_dotnet')) {
					$obj = new \COM('scripting.filesystemobject');
					if (is_object($obj)) {
						$ref = $obj->getfolder($dir);
						$totalSize = $ref->size;
						$obj = null;

						return $totalSize;
					}
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
	public function get_total_space()
	{
		//TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$dts = disk_total_space(base_path(''));

		return $this->getSymbolByQuantity($dts);
	}

	/**
	 * Return the free space available on / (we assume we run on / ).
	 *
	 * @return string
	 */
	public function get_free_space()
	{
		//TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$dfs = disk_free_space(base_path(''));

		return $this->getSymbolByQuantity($dfs);
	}

	/**
	 * Return the percentage of free space available on / (we assume we run on / ).
	 *
	 * @return string
	 */
	public function get_free_percent()
	{
		//TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$dts = disk_total_space(base_path(''));
		$dfs = disk_free_space(base_path(''));

		return floor(100 * $dfs / $dts) . '%';
	}

	/**
	 * Return the space taken by the Lychee installation.
	 *
	 * @return string
	 */
	public function get_lychee_space()
	{
		$ds = $this->getTotalSize(base_path(''));

		return $this->getSymbolByQuantity($ds);
	}

	/**
	 * Return the space taken by the upload folder (Big, Medium, Small, Thumbs).
	 *
	 * @return string
	 */
	public function get_lychee_upload_space()
	{
		//TODO : FIX TO USE STORAGE FACADE => uploads may not be in public/uploads
		$ds = $this->getTotalSize(base_path('public/uploads/'));

		return $this->getSymbolByQuantity($ds);
	}
}