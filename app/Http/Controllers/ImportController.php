<?php
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\PhotoFunctions;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use ImagickException;

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
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 * @param AlbumFunctions $albumFunctions
	 */
	public function __construct(PhotoFunctions $photoFunctions, AlbumFunctions $albumFunctions)
	{
		$this->photoFunctions = $photoFunctions;
		$this->albumFunctions = $albumFunctions;
	}



	/**
	 * Creates an array similar to a file upload array and adds the photo to Lychee.
	 * @param $path
	 * @param int $albumID
	 * @return boolean Returns true when photo import was successful.
	 */
	private function photo($path, $albumID = 0)
	{
		// No need to validate photo type and extension in this function.
		// $photo->add will take care of it.
		$info = getimagesize($path);

		$nameFile = array();
		$nameFile['name'] = $path;
		$nameFile['type'] = $info['mime'];
		$nameFile['tmp_name'] = $path;

		if ($this->photoFunctions->add($nameFile, $albumID) === false) {
			return false;
		}
		return true;
	}



	/**
	 * @param Request $request
	 * @return false|string
	 */
	public function url(Request $request)
	{
		$request->validate([
			'url'     => 'string|required',
			'albumID' => 'string|required'
		]);

		// Check permissions
		if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_IMPORT')) === false) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');
			return Response::error('An upload-folder is missing or not readable and writable!');
		}

		$urls = $request['url'];

		$error = false;
		// Parse URLs
		$urls = str_replace(' ', '%20', $urls);
		$urls = explode(',', $urls);

		foreach ($urls as &$url) {
			// Validate photo type and extension even when $this->photo (=> $photo->add) will do the same.
			// This prevents us from downloading invalid photos.
			// Verify extension
			$extension = Helpers::getExtension($url, true);
			if (!$this->photoFunctions->isValidExtension($extension)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo format not supported ('.$url.')');
				continue;
			}
			// Verify image
			$type = @exif_imagetype($url);
			if (!$this->photoFunctions->isValidImageType($type)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo type not supported ('.$url.')');
				continue;
			}
			$filename = pathinfo($url, PATHINFO_FILENAME).$extension;
			$tmp_name = Config::get('defines.dirs.LYCHEE_UPLOADS_IMPORT').$filename;
			if (@copy($url, $tmp_name) === false) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not copy file ('.$url.') to temp-folder ('.$tmp_name.')');
				continue;
			}
			// Import photo
			if (!$this->photo($tmp_name, $request['albumID'])) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not import file ('.$tmp_name.')');
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
	 * @return bool|string
	 * @throws ImagickException
	 */
	public function server(Request $request)
	{

		$request->validate([
			'path'    => 'string|required',
			'albumID' => 'int|required'
		]);

		$php_script_no_limit = Configs::get_value('php_script_no_limit', '0');
		if ($php_script_no_limit == '1') {
			set_time_limit(0);
			Logs::notice(__METHOD__, __LINE__, 'Importing using unlimited execution time');
		}

		return $this->server_exec($request['path'], $request['albumID']);

	}



	/**
	 * @param string $path
	 * @param integer $albumID
	 * @return boolean|string Returns true when successful.
	 *                        Warning: Folder empty or no readable files to process!
	 *                        Notice: Import only contained albums!
	 * @throws ImagickException
	 */
	// I switched this to private, as it should not be needed to be public. if it breaks something we will double check.
	private function server_exec(string $path, $albumID)
	{

		// Parse path
		if (!isset($path)) {
			$path = Config::get('defines.dirs.LYCHEE_UPLOADS_IMPORT');
		}
		if (substr($path, -1) === '/') {
			$path = substr($path, 0, -1);
		}
		if (is_dir($path) === false) {
			Logs::error(__METHOD__, __LINE__, 'Given path is not a directory ('.$path.')');
			return 'false';
		}

		// Skip folders of Lychee
		if ($path === Config::get('defines.dirs.LYCHEE_UPLOADS_BIG') || ($path.'/') === Config::get('defines.dirs.LYCHEE_UPLOADS_BIG') ||
			$path === Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM') || ($path.'/') === Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM') ||
			$path === Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL') || ($path.'/') === Config::get('defines.dirs.LYCHEE_UPLOADS_SMALL') ||
			$path === Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB') || ($path.'/') === Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB')) {
			Logs::error(__METHOD__, __LINE__, 'The given path is a reserved path of Lychee ('.$path.')');
			return 'false';
		}

		$error = false;
		$contains['photos'] = false;
		$contains['albums'] = false;

		// Get all files
		$files = glob($path.'/*');
		foreach ($files as $file) {
			// It is possible to move a file because of directory permissions but
			// the file may still be unreadable by the user
			if (!is_readable($file)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not read file or directory ('.$file.')');
				continue;
			}
			if (@exif_imagetype($file) !== false) {
				// Photo
				$contains['photos'] = true;
				if ($this->photo($file, $albumID) === false) {
					$error = true;
					Logs::error(__METHOD__, __LINE__, 'Could not import file ('.$file.')');
					continue;
				}
			}
			else {
				if (is_dir($file)) {

					// Album creation

					// Folder
					$album = $this->albumFunctions->create(basename($file), $albumID);
					// this actually should not fail.
					if ($album === false) {
						$error = true;
						Logs::error(__METHOD__, __LINE__, 'Could not create album in Lychee ('.basename($file).')');
						continue;
					}
					$newAlbumID = $album->id;
					$contains['albums'] = true;
					$import = $this->server_exec($file.'/', $newAlbumID);
					if ($import !== 'true' && $import !== 'Notice: Import only contains albums!') {
						$error = true;
						Logs::error(__METHOD__, __LINE__, 'Could not import folder. Function returned warning.');
						continue;
					}
				}
			}
		}

		// The following returns will be caught in the front-end
		if ($contains['photos'] === false && $contains['albums'] === false) {
			return 'Warning: Folder empty or no readable files to process!';
		}
		if ($contains['photos'] === false && $contains['albums'] === true) {
			return 'Notice: Import only contained albums!';
		}
		if ($error === true) {
			return 'false';
		}
		return 'true';
	}
}