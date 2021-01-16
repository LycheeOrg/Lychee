<?php

namespace App\Actions\Import;

use App\Actions\Import\Extensions\ImportPhoto;
use App\Actions\Photo\Extensions\Constants;
use App\Assets\Helpers;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Storage;

class Exec
{
	use ImportPhoto;
	use Constants;

	public $force_skip_duplicates = false;
	public $resync_metadata = false;
	public $delete_imported;

	public $memCheck = true;
	public $statusCLIFormatting = false;
	public $memLimit;
	public $memWarningGiven = false;

	/**
	 * Output status update to stdout (from where StreamedResponse picks it up).
	 * Every line of output is terminated with a newline so that the front end
	 * can be sure that it's complete.
	 * The status can be one of:
	 * - Status: <directory name>: <percentage of completion>
	 *   (A status is always sent for 0 and 100 percent at least).
	 * - Problem: <file or directory name>: <problem description>
	 *   (We avoid starting a line with 'Error' as that has a special meaning
	 *   in the front end, preventing the completion callback from being
	 *   invoked).
	 * - Warning: Approaching memory limit.
	 */
	private function status_update(string $status)
	{
		if (!$this->statusCLIFormatting) {
			// We append a newline to the status string, JSON-encode the
			// result, and strip the  surrounding '"' characters since this
			// isn't a complete JSON string yet.
			echo substr(json_encode($status . "\n"), 1, -1);
			if (ob_get_level() > 0) {
				ob_flush();
			}
			flush();
		} else {
			echo $status . PHP_EOL;
		}
	}

	private function parsePath(string &$path, string $origPath)
	{
		if (!isset($path)) {
			// @codeCoverageIgnoreStart
			$path = Storage::path('import');
			// @codeCoverageIgnoreEnd
		}
		if (substr($path, -1) === '/') {
			$path = substr($path, 0, -1);
		}
		if (is_dir($path) === false) {
			$this->status_update('Problem: ' . $origPath . ': Given path is not a directory');
			Logs::error(__METHOD__, __LINE__, 'Given path is not a directory (' . $origPath . ')');

			return false;
		}

		// Skip folders of Lychee
		if (
			realpath($path) === Storage::path('big') ||
			realpath($path) === Storage::path('medium') ||
			realpath($path) === Storage::path('small') ||
			realpath($path) === Storage::path('thumb')
		) {
			$this->status_update('Problem: ' . $origPath . ': Given path is reserved');
			Logs::error(__METHOD__, __LINE__, 'The given path is a reserved path of Lychee (' . $origPath . ')');

			return false;
		}

		return true;
	}

	private function setUpIgnoreList($path, $ignore_list)
	{
		// Let's load the list of filenames to ignore
		if (file_exists($path . '/.lycheeignore')) {
			$ignore_list_new = file($path . '/.lycheeignore');
			if (isset($ignore_list)) {
				$ignore_list = array_merge($ignore_list, $ignore_list_new);
			} else {
				$ignore_list = $ignore_list_new;
			}
		}

		return $ignore_list;
	}

	private function checkAgainstIgnoreList($file, $ignore_list): bool
	{
		if (!isset($ignore_list)) {
			return false;
		}

		$ignore_file = false;

		foreach ($ignore_list as $value_ignore) {
			if ($this->check_file_matches_pattern(basename($file), $value_ignore)) {
				$ignore_file = true;
				break;
			}
		}

		return $ignore_file;
	}

	private function memWarningCheck()
	{
		if ($this->memCheck && !$this->memWarningGiven && memory_get_usage() > $this->memLimit) {
			// @codeCoverageIgnoreStart
			$this->status_update('Warning: Approaching memory limit');
			$this->memWarningGiven = true;
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * @param string $path
	 * @param int    $albumID
	 * @param array  $ignore_list
	 *
	 * @throws ImagickException
	 */
	public function do(
		string $path,
		$albumID,
		$ignore_list = null
	) {
		// Parse path
		$origPath = $path;

		if (!$this->parsePath($path, $origPath)) {
			return;
		}

		// We process breadth-first: first all the files in a directory,
		// then the subdirectories.  This way, if the process fails along the
		// way, it's much easier for the user to figure out what was imported
		// and what was not.
		$this->setUpIgnoreList($path, $ignore_list);

		$files = glob($path . '/*');
		$filesTotal = count($files);
		$filesCount = 0;
		$dirs = [];
		$lastStatus = microtime(true);

		// Add '%' at end for CLI output
		$percent_symbol = ($this->statusCLIFormatting) ? '%' : '';

		$this->status_update('Status: ' . $origPath . ': 0' . $percent_symbol);
		foreach ($files as $file) {
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Report if we might be running out of memory.
			$this->memWarningCheck();

			// Generate the status at most once a second, except for 0% and
			// 100%, which are always generated.
			$time = microtime(true);
			if ($time - $lastStatus >= 1) {
				$this->status_update('Status: ' . $origPath . ': ' . intval($filesCount / $filesTotal * 100) . $percent_symbol);
				$lastStatus = $time;
			}

			// Let's check if we should ignore the file
			if ($this->checkAgainstIgnoreList($file, $ignore_list)) {
				$filesTotal--;
				continue;
			}

			if (is_dir($file)) {
				$dirs[] = $file;
				$filesTotal--;
				continue;
			}

			$filesCount++;
			// It is possible to move a file because of directory permissions but
			// the file may still be unreadable by the user
			if (!is_readable($file)) {
				$this->status_update('Problem: ' . $file . ': Could not read file');
				Logs::error(__METHOD__, __LINE__, 'Could not read file or directory (' . $file . ')');
				continue;
			}
			$extension = Helpers::getExtension($file, true);
			$raw_formats = strtolower(Configs::get_value('raw_formats', ''));
			$is_raw = in_array(strtolower($extension), explode('|', $raw_formats), true);
			if (@exif_imagetype($file) !== false || in_array(strtolower($extension), $this->validExtensions, true) || $is_raw) {
				// Photo or Video
				if ($this->photo($file, $this->delete_imported, $albumID, $this->force_skip_duplicates, $this->resync_metadata) === false) {
					$this->status_update('Problem: ' . $file . ': Could not import file');
					Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $file . ')');
				}
			} else {
				$this->status_update('Problem: ' . $file . ': Unsupported file type');
				Logs::error(__METHOD__, __LINE__, 'Unsupported file type (' . $file . ')');
			}
		}
		$this->status_update('Status: ' . $origPath . ': 100' . $percent_symbol);

		// Album creation
		foreach ($dirs as $dir) {
			// Folder
			$album = null;
			if ($this->force_skip_duplicates || Configs::get_value('skip_duplicates', '0') === '1') {
				$album = Album::where('parent_id', '=', $albumID == 0 ? null : $albumID)
					->where('title', '=', basename($dir))
					->get()
					->first();
			}
			if ($album === null) {
				$create = resolve(AlbumCreate::class);
				$album = $create->create(basename($dir), $albumID);
				// this actually should not fail.
				if ($album === false) {
					// @codeCoverageIgnoreStart

					$this->status_update('Problem: ' . basename($dir) . ': Could not create album');
					Logs::error(__METHOD__, __LINE__, 'Could not create album in Lychee (' . basename($dir) . ')');
					continue;
				}
				// @codeCoverageIgnoreEnd
			}
			$newAlbumID = $album->id;
			$this->do($dir . '/', $newAlbumID, $ignore_list);
		}
	}

	/**
	 * @param string $pattern
	 * @param string $filename
	 *
	 * @return bool
	 */
	private function check_file_matches_pattern(string $pattern, string $filename)
	{
		// This function checks if the given filename matches the pattern allowing for
		// star als wildcard (as in *.jpg)
		// Example: '*.jpg' matches all jpgs

		$pattern = preg_replace_callback('/([^*])/', [$this, 'preg_quote_callback_fct'], $pattern);
		$pattern = str_replace('*', '.*', $pattern);

		return (bool) preg_match('/^' . $pattern . '$/i', $filename);
	}

	/**
	 * @param array $my_array
	 *
	 * @return string
	 */
	private function preg_quote_callback_fct(array $my_array)
	{
		return preg_quote($my_array[1], '/');
	}
}
