<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Response;
use Illuminate\Http\Request;
use ImagickException;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	private $memLimit;
	private $memWarningGiven;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions   $photoFunctions
	 * @param AlbumFunctions   $albumFunctions
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(PhotoFunctions $photoFunctions, AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions)
	{
		$this->photoFunctions = $photoFunctions;
		$this->albumFunctions = $albumFunctions;
		$this->sessionFunctions = $sessionFunctions;
	}

	/**
	 * Creates an array similar to a file upload array and adds the photo to Lychee.
	 *
	 * @param $path
	 * @param bool $delete_imported
	 * @param int  $albumID
	 *
	 * @return bool returns true when photo import was successful
	 */
	private function photo($path, $delete_imported, $albumID = 0)
	{
		// No need to validate photo type and extension in this function.
		// $photo->add will take care of it.
		$mime = mime_content_type($path);

		$nameFile = [];
		$nameFile['name'] = $path;
		$nameFile['type'] = $mime;
		$nameFile['tmp_name'] = $path;

		if ($this->photoFunctions->add($nameFile, $albumID, $delete_imported) === false) {
			return false;
		}

		return true;
	}

	/**
	 * @param Request $request
	 *
	 * @return false|string
	 */
	public function url(Request $request)
	{
		$request->validate([
			'url' => 'string|required',
			'albumID' => 'string|required',
		]);

		// Check permissions
		if (Helpers::hasPermissions(Storage::path('import') === false)) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');

			return Response::error('An upload-folder is missing or not readable and writable!');
		}

		$urls = $request['url'];

		$error = false;
		// Parse URLs
		$urls = str_replace(' ', '%20', $urls);
		$urls = explode(',', $urls);

		foreach ($urls as &$url) {
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Validate photo type and extension even when $this->photo (=> $photo->add) will do the same.
			// This prevents us from downloading invalid photos.
			// Verify extension
			$extension = Helpers::getExtension($url, true);
			if (!$this->photoFunctions->isValidExtension($extension)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo format not supported (' . $url . ')');
				continue;
			}
			// Verify image
			$type = @exif_imagetype($url);
			if (!$this->photoFunctions->isValidImageType($type) && !in_array(strtolower($extension), $this->photoFunctions->validExtensions, true)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo type not supported (' . $url . ')');
				continue;
			}
			$filename = pathinfo($url, PATHINFO_FILENAME) . $extension;
			$tmp_name = Storage::path('import/' . $filename);
			if (@copy($url, $tmp_name) === false) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not copy file (' . $url . ') to temp-folder (' . $tmp_name . ')');
				continue;
			}
			// Import photo
			if (!$this->photo($tmp_name, true, $request['albumID'])) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $tmp_name . ')');
				continue;
			}
		}
		// Call plugins
		if ($error === false) {
			return 'true';
		}

		return 'false';
	}

	/**
	 * @param Request $request
	 *
	 * @return bool|string
	 *
	 * @throws ImagickException
	 */
	public function server(Request $request)
	{
		$request->validate([
			'path' => 'string|required',
			'albumID' => 'int|required',
			'delete_imported' => 'int',
		]);

		if (isset($request['delete_imported'])) {
			$delete_imported = $request['delete_imported'] === '1';
		} else {
			$delete_imported = Configs::get_value('delete_imported', '0') === '1';
		}

		// memory_limit can have a K/M/etc suffix which makes querying it
		// more complicated...
		if (sscanf(ini_get('memory_limit'), '%d%c', $this->memLimit, $memExt) === 2) {
			switch (strtolower($memExt)) {
				case 'k':
					$this->memLimit *= 1024;
					break;
				case 'm':
					$this->memLimit *= 1024 * 1024;
					break;
				case 'g':
					$this->memLimit *= 1024 * 1024 * 1024;
					break;
				case 't':
					$this->memLimit *= 1024 * 1024 * 1024 * 1024;
					break;
			}
		}
		// We set the warning threshold at 90% of the limit.
		$this->memLimit = intval($this->memLimit * 0.9);
		$this->memWarningGiven = false;

		$response = new StreamedResponse();
		$response->setCallback(function () use ($request, $delete_imported) {
			// Surround the response in '"' characters to make it a valid
			// JSON string.
			echo '"';
			$this->server_exec($request['path'], $request['albumID'], $delete_imported);
			echo '"';
		});
		// nginx-specific voodoo, as per https://symfony.com/doc/current/components/http_foundation.html#streaming-a-response
		$response->headers->set('X-Accel-Buffering', 'no');

		return $response;
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
		// We append a newline to the status string, JSON-encode the
		// result, and strip the  surrounding '"' characters since this
		// isn't a complete JSON string yet.
		echo substr(json_encode($status . "\n"), 1, -1);
		ob_flush();
		flush();
	}

	/**
	 * @param string $path
	 * @param int    $albumID
	 * @param bool   $delete_imported
	 * @param array  $ignore_list
	 *
	 * @throws ImagickException
	 */
	private function server_exec(string $path, $albumID, $delete_imported, $ignore_list = null)
	{
		// Parse path
		$origPath = $path;
		if (!isset($path)) {
			$path = Storage::path('import');
		}
		if (substr($path, -1) === '/') {
			$path = substr($path, 0, -1);
		}
		if (is_dir($path) === false) {
			$this->status_update('Problem: ' . $origPath . ': Given path is not a directory');
			Logs::error(__METHOD__, __LINE__, 'Given path is not a directory (' . $origPath . ')');

			return;
		}

		// Skip folders of Lychee
		if ($path === Storage::path('big') ||
			$path === Storage::path('medium') ||
			$path === Storage::path('small') ||
			$path === Storage::path('thumb')) {
			$this->status_update('Problem: ' . $origPath . ': Given path is reserved');
			Logs::error(__METHOD__, __LINE__, 'The given path is a reserved path of Lychee (' . $origPath . ')');

			return;
		}

		// We process breadth-first: first all the files in a directory,
		// then the subdirectories.  This way, if the process fails along the
		// way, it's much easier for the user to figure out what was imported
		// and what was not.

		// Let's load the list of filenames to ignore
		if (file_exists($path . '/.lycheeignore')) {
			$ignore_list_new = file($path . '/.lycheeignore');
			if (isset($ignore_list)) {
				$ignore_list = array_merge($ignore_list, $ignore_list_new);
			} else {
				$ignore_list = $ignore_list_new;
			}
		}

		$files = glob($path . '/*');
		$filesTotal = count($files);
		$filesCount = 0;
		$dirs = [];
		$lastStatus = microtime(true);
		$this->status_update('Status: ' . $origPath . ': 0');
		foreach ($files as $file) {
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Report if we might be running out of memory.
			if (!$this->memWarningGiven && memory_get_usage() > $this->memLimit) {
				$this->status_update('Warning: Approaching memory limit');
				$this->memWarningGiven = true;
			}

			// Generate the status at most once a second, except for 0% and
			// 100%, which are always generated.
			$time = microtime(true);
			if ($time - $lastStatus >= 1) {
				$this->status_update('Status: ' . $origPath . ': ' . intval($filesCount / $filesTotal * 100));
				$lastStatus = $time;
			}

			// Let's check if we should ignore the file

			if (isset($ignore_list)) {
				$ignore_file = false;

				foreach ($ignore_list as $value_ignore) {
					if ($this->check_file_matches_pattern(basename($file), $value_ignore) == true) {
						$ignore_file = true;
						break;
					}
				}

				if ($ignore_file == true) {
					$filesTotal--;
					continue;
				}
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
			if (@exif_imagetype($file) !== false || in_array(strtolower($extension), $this->photoFunctions->validExtensions, true)) {
				// Photo or Video
				if ($this->photo($file, $delete_imported, $albumID) === false) {
					$this->status_update('Problem: ' . $file . ': Could not import file');
					Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $file . ')');
					continue;
				}
			} else {
				$this->status_update('Problem: ' . $file . ': Unsupported file type');
				Logs::error(__METHOD__, __LINE__, 'Unsupported file type (' . $file . ')');
				continue;
			}
		}
		$this->status_update('Status: ' . $origPath . ': 100');

		// Album creation
		foreach ($dirs as $dir) {
			// Folder
			$album = null;
			if (Configs::get_value('skip_duplicates', '0') === '1') {
				$album = Album::where('parent_id', '=', $albumID == 0 ? null : $albumID)
					->where('title', '=', basename($dir))
					->get()
					->first();
			}
			if ($album === null) {
				$album = $this->albumFunctions->create(basename($dir), $albumID, $this->sessionFunctions->id());
				// this actually should not fail.
				if ($album === false) {
					$this->status_update('Problem: ' . $basename($dir) . ': Could not create album');
					Logs::error(__METHOD__, __LINE__, 'Could not create album in Lychee (' . basename($dir) . ')');
					continue;
				}
			}
			$newAlbumID = $album->id;
			$this->server_exec($dir . '/', $newAlbumID, $delete_imported, $ignore_list);
		}
	}

	/**
	 * @param array $my_array
	 *
	 * @return string
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
