<?php

namespace App\Actions\Import;

use App\Actions\Album\Create as AlbumCreate;
use App\Actions\Photo\Create as PhotoCreate;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;
use App\Facades\Helpers;
use App\Image\NativeLocalFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Exec
{
	use Constants;

	// TODO: Refactor this and use `ImportMode` instead of four boolean properties
	public $skip_duplicates = false;
	public $resync_metadata = false;
	public $delete_imported;
	public $import_via_symlink;

	public $memCheck = true;
	public $statusCLIFormatting = false;
	public $memLimit;
	public $memWarningGiven = false;

	private $raw_formats = [];

	public function __construct()
	{
		$this->raw_formats = explode('|', strtolower(Configs::get_value('raw_formats', '')));
	}

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
			echo substr($status, strpos($status, ' ') + 1) . PHP_EOL;
		}
	}

	private function status_progress(string $path, string $msg)
	{
		$this->status_update('Status: ' . $path . ': ' . $msg);
	}

	private function status_warning(string $msg)
	{
		$this->status_update('Warning: ' . $msg);
	}

	private function status_error(string $path, string $msg)
	{
		$this->status_update('Problem: ' . $path . ': ' . $msg);
	}

	private function parsePath(string &$path, string $origPath)
	{
		if (!isset($path)) {
			// @codeCoverageIgnoreStart
			$path = Storage::path('import');
			// @codeCoverageIgnoreEnd
		}
		if (str_ends_with($path, '/')) {
			$path = substr($path, 0, -1);
		}
		if (is_dir($path) === false) {
			$this->status_error($origPath, 'Given path is not a directory');
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
			$this->status_error($origPath, 'Given path is reserved');
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
			$this->status_warning('Approaching memory limit');
			$this->memWarningGiven = true;
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * @param string      $path
	 * @param string|null $albumID
	 * @param string[]    $ignore_list
	 */
	public function do(
		string $path,
		?string $albumID,
		array $ignore_list = []
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

		// Update ignore list
		$ignore_list = $this->setUpIgnoreList($path, $ignore_list);

		// TODO: Consider to use a modern OO-approach using [`DirectoryIterator`](https://www.php.net/manual/en/class.directoryiterator.php) and [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)
		/** @var string[] $files */
		$files = glob($path . '/*');
		if ($files === false) {
			$this->status_error($origPath, 'Could not list directory entries');
			Logs::error(__METHOD__, __LINE__, 'Could not list directory entries (' . $path . ')');

			return;
		}

		$filesTotal = count($files);
		$filesCount = 0;
		$dirs = [];
		$lastStatus = microtime(true);

		// Add '%' at end for CLI output
		$percent_symbol = ($this->statusCLIFormatting) ? '%' : '';

		$this->status_progress($origPath, '0' . $percent_symbol);
		foreach ($files as $file) {
			// re-read session in case cancelling import was requested
			session()->start();
			if (Session::has('cancel')) {
				Session::forget('cancel');
				$this->status_error($origPath, 'Import cancelled');
				Logs::warning(__METHOD__, __LINE__, 'Import cancelled');

				return;
			}
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Report if we might be running out of memory.
			$this->memWarningCheck();

			// Generate the status at most once a second, except for 0% and
			// 100%, which are always generated.
			$time = microtime(true);
			if ($time - $lastStatus >= 1) {
				$this->status_progress($origPath, intval($filesCount / $filesTotal * 100) . $percent_symbol);
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
			// TODO: This check will be unnecessary, after we have proper exception handling, because we try to read streams
			if (!is_readable($file)) {
				$this->status_error($file, 'Could not read file');
				Logs::error(__METHOD__, __LINE__, 'Could not read file or directory (' . $file . ')');
				continue;
			}
			$extension = Helpers::getExtension($file, false);
			$is_raw = in_array(strtolower($extension), $this->raw_formats, true);
			// TODO: Consolidate all mimetype/extension handling in one place; here we have another test whether the source file is supported which is inconsistent with tests elsewhere
			// TODO: Probably the best place is \App\Image\MediaFile.
			// TODO: Consider to make this test a general part of \App\Actions\Photo\Create::add. Then we don't need those tests at multiple places.
			if (@exif_imagetype($file) !== false || in_array(strtolower($extension), $this->validExtensions, true) || $is_raw) {
				// Photo or Video
				try {
					// TODO: Refactor this, rationale see below
					// This is not the way how `PhotoCreate` is supposed
					// to be used.
					// Actually, an instance of the class should only
					// be created once using a single instance of
					// `ImportMode` and then `PhotoCreate::add` should
					// be called for each file.
					$photoCreate = new PhotoCreate(new ImportMode(
						$this->delete_imported,
						$this->skip_duplicates,
						$this->import_via_symlink,
						$this->resync_metadata
					));
					if (
						$photoCreate->add(SourceFileInfo::createByLocalFile(new NativeLocalFile($file)), $albumID) == null
					) {
						$this->status_error($file, 'Could not import file');
						Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $file . ')');
					}
				} catch (PhotoSkippedException $e) {
					$this->status_error($file, 'Skipped duplicate');
				} catch (PhotoResyncedException $e) {
					$this->status_error($file, 'Skipped duplicate (resynced metadata)');
				} catch (Exception $e) {
					$this->status_error($file, 'Could not import file');
					Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $file . '): ' . $e->getMessage());
				}
			} else {
				$this->status_error($file, 'Unsupported file type');
				Logs::error(__METHOD__, __LINE__, 'Unsupported file type (' . $file . ')');
			}
		}
		$this->status_progress($origPath, '100' . $percent_symbol);

		// Album creation
		foreach ($dirs as $dir) {
			// Folder
			$album = null;
			if ($this->skip_duplicates) {
				$album = Album::query()
					->select(['albums.*'])
					->join('base_albums', 'base_albums.id', '=', 'albums.id')
					->where('albums.parent_id', '=', $albumID)
					->where('base_albums.title', '=', basename($dir))
					->get()
					->first();
			}
			if ($album === null) {
				$create = resolve(AlbumCreate::class);
				$album = $create->create(basename($dir), $albumID);
				// this actually should not fail.
				if ($album === false) {
					// @codeCoverageIgnoreStart

					$this->status_error(basename($dir), ': Could not create album');
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
		// star as wildcard (as in *.jpg)
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
