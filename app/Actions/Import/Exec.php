<?php

namespace App\Actions\Import;

use App\Actions\Album\Create as AlbumCreate;
use App\Actions\Photo\Create as PhotoCreate;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\PhotoSkippedException;
use App\Facades\Helpers;
use App\Image\NativeLocalFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Exec
{
	use Constants;

	public const REPORT_TYPE_PROGRESS = 'progress';
	public const REPORT_TYPE_WARNING = 'warning';
	public const REPORT_TYPE_ERROR = 'error';

	public ImportMode $importMode;
	public bool $memCheck = true;
	public bool $statusCLIFormatting = false;
	public int $memLimit = 0;
	public bool $memWarningGiven = false;
	private array $raw_formats;
	private bool $firstReportGiven = false;

	public function __construct()
	{
		$this->raw_formats = explode('|', strtolower(Configs::get_value('raw_formats', '')));
	}

	/**
	 * Output status update to stdout.
	 *
	 * The output is either send to a web-client via {@link StreamedResponse}
	 * or to the CLI.
	 *
	 * For web-clients this method reports the JSON objects like
	 *
	 *    { "type": type, "key": key, "message": message }
	 *
	 * The outer caller precedes and terminates the whole output by `[` and
	 * `]`, resp., in order to indicate the start and beginning of a JSON
	 * array.
	 * This method also inserts the commas between objects.
	 *
	 * For CLI output we print lines like `key: message`, if key is not `null`,
	 * or simply `message` if the key equals `null`.
	 * The lines are terminated by a newline character.
	 *
	 * Only the following three cases are supported:
	 *
	 *  1. `$type === Exec::REPORT_TYPE_PROGRESS`: In this case, `$key`
	 *     indicates a directory name and `$message` is an integer between
	 *     0 and 100 (without a percentage sign) which indicates the progress
	 *     for the indicated directory
	 *  2. `$type === Exec::REPORT_TYPE_ERROR`: In this case, `$key`
	 *     indicates a directory or file name and `$message` is contains
	 *     the error message
	 *  3. `$type === Exec::REPORT_TYPE_WARNING`: In this case, `$key`
	 *     equals `null` and the message contains a global warning message.
	 *
	 * @param string      $type    either {@link Exec::REPORT_TYPE_PROGRESS},
	 *                             {@link Exec::REPORT_TYPE_WARNING}, or
	 *                             {@link Exec::REPORT_TYPE_ERROR}
	 * @param string|null $key     the name of the directory of file which is
	 *                             associated to the report; note a web-client
	 *                             uses the key, to group subsequent messages
	 *                             for the same directory/file together or to
	 *                             only display the latest message
	 * @param string|int  $message the message
	 *
	 * @return void
	 */
	private function report(string $type, ?string $key, string|int $message): void
	{
		if (!$this->statusCLIFormatting) {
			try {
				if ($this->firstReportGiven) {
					echo ',';
				}
				echo json_encode([
					'type' => $type,
					'key' => $key,
					'message' => $message,
				], JSON_THROW_ON_ERROR);
				$this->firstReportGiven = true;
				if (ob_get_level() > 0) {
					ob_flush();
				}
				flush();
			} catch (\JsonException) {
				// do nothing
			}
		} else {
			echo $key . ($key ? ': ' : '') . $message . ($type === self::REPORT_TYPE_PROGRESS ? '%' : '') . PHP_EOL;
		}
	}

	private function reportProgress(string $path, int $percentage)
	{
		$this->report(self::REPORT_TYPE_PROGRESS, $path, $percentage);
	}

	private function reportWarning(string $msg)
	{
		$this->report(self::REPORT_TYPE_WARNING, null, $msg);
	}

	private function reportError(string $path, string $msg)
	{
		$this->report(self::REPORT_TYPE_ERROR, $path, $msg);
	}

	private function parsePath(string &$path, string $origPath): bool
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
			$this->reportError($origPath, 'Given path is not a directory');
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
			$this->reportError($origPath, 'Given path is reserved');
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
			$this->reportWarning('Approaching memory limit');
			$this->memWarningGiven = true;
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * @param string     $path
	 * @param Album|null $parentAlbum
	 * @param string[]   $ignore_list
	 */
	public function do(
		string $path,
		?Album $parentAlbum,
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
			$this->reportError($origPath, 'Could not list directory entries');
			Logs::error(__METHOD__, __LINE__, 'Could not list directory entries (' . $path . ')');

			return;
		}

		$filesTotal = count($files);
		$filesCount = 0;
		$dirs = [];
		$lastStatus = microtime(true);

		$this->reportProgress($origPath, 0);
		foreach ($files as $file) {
			// re-read session in case cancelling import was requested
			session()->start();
			if (Session::has('cancel')) {
				Session::forget('cancel');
				$this->reportError($origPath, 'Import cancelled');
				Logs::warning(__METHOD__, __LINE__, 'Import cancelled');

				return;
			}
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Report if we might be running out of memory.
			$this->memWarningCheck();

			// Generate the status at most each third of a second,
			// except for 0% and 100%, which are always generated.
			// Generating more frequently would create unnecessary many status
			// reports; generating less frequently might lead to Firefox
			// complaining.
			// Firefox considers any response with a delay of >=500ms as
			// "unresponsive".
			// Taking additional delays on the network layer into account,
			// 1/3 second should be fine.
			$time = microtime(true);
			if ($time - $lastStatus >= 0.3) {
				$this->reportProgress($origPath, intval($filesCount / $filesTotal * 100));
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
				$this->reportError($file, 'Could not read file');
				Logs::error(__METHOD__, __LINE__, 'Could not read file or directory (' . $file . ')');
				continue;
			}
			$extension = Helpers::getExtension($file, false);
			$is_raw = in_array(strtolower($extension), $this->raw_formats, true);
			try {
				// TODO: Consolidate all mimetype/extension handling in one place; here we have another test whether the source file is supported which is inconsistent with tests elsewhere
				// TODO: Probably the best place is \App\Image\MediaFile.
				// TODO: Consider to make this test a general part of \App\Actions\Photo\Create::add. Then we don't need those tests at multiple places.
				// Note: `exif_imagetype` may also throw an exception
				// (instead of returning `false`), if the file is too small
				// to read enough bytes to determine the file type.
				// So we put `exif_imagetype` last in the condition and
				// exploit lazy evaluation of boolean terms for the case
				// that we import a "short" raw file.
				if ($is_raw || in_array(strtolower($extension), $this->validExtensions, true) || exif_imagetype($file) !== false) {
					// Photo or Video
					// TODO: Refactor this, rationale see below
					// This is not the way how `PhotoCreate` is supposed
					// to be used.
					// Actually, an instance of the class should only
					// be created once using a single instance of
					// `ImportMode` and then `PhotoCreate::add` should
					// be called for each file.
					$photoCreate = new PhotoCreate($this->importMode);
					$photoCreate->add(SourceFileInfo::createByLocalFile(new NativeLocalFile($file)), $parentAlbum);
				} else {
					$this->reportError($file, 'Unsupported file type');
					Logs::error(__METHOD__, __LINE__, 'Unsupported file type (' . $file . ')');
				}
			} catch (PhotoSkippedException $e) {
				$this->reportError($file, $e->getMessage());
			} catch (\Throwable $e) {
				$this->reportError($file, 'Could not import file');
				Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $file . ')');
			}
		}
		$this->reportProgress($origPath, 100);

		// Album creation
		foreach ($dirs as $dir) {
			// Folder
			$album = null;
			if ($this->importMode->shallSkipDuplicates()) {
				$album = Album::query()
					->select(['albums.*'])
					->join('base_albums', 'base_albums.id', '=', 'albums.id')
					->where('albums.parent_id', '=', $parentAlbum->id)
					->where('base_albums.title', '=', basename($dir))
					->get()
					->first();
			}
			if ($album === null) {
				$create = resolve(AlbumCreate::class);
				$album = $create->create(basename($dir), $parentAlbum);
				// this actually should not fail.
				if ($album === false) {
					// @codeCoverageIgnoreStart

					$this->reportError(basename($dir), ': Could not create album');
					Logs::error(__METHOD__, __LINE__, 'Could not create album in Lychee (' . basename($dir) . ')');
					continue;
				}
				// @codeCoverageIgnoreEnd
			}
			$this->do($dir . '/', $album, $ignore_list);
		}
	}

	/**
	 * @param string $pattern
	 * @param string $filename
	 *
	 * @return bool
	 */
	private function check_file_matches_pattern(string $pattern, string $filename): bool
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
	private function preg_quote_callback_fct(array $my_array): string
	{
		return preg_quote($my_array[1], '/');
	}
}
