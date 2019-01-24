<?php

namespace App\Http\Controllers;

use App\Logs;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ImportController extends Controller
{

	/**
	 * Creates an array similar to a file upload array and adds the photo to Lychee.
	 * @param $path
	 * @param int $albumID
	 * @return boolean Returns true when photo import was successful.
	 * @throws \ImagickException
	 */
	static private function photo($path, $albumID = 0)
	{
		// No need to validate photo type and extension in this function.
		// $photo->add will take care of it.
		$info = getimagesize($path);
//		$size = filesize($path);
//		$photo = new Photo(null);

		$nameFile = array();
		$nameFile['name'] = $path;
		$nameFile['type'] = $info['mime'];
		$nameFile['tmp_name'] = $path;

		if (PhotoFunctions::add($nameFile, $albumID) === false) {
			return false;
		}
		return true;
	}



	static public function url(Request $request)
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
			if (!in_array(strtolower($extension), PhotoFunctions::$validExtensions, true)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo format not supported ('.$url.')');
				continue;
			}
			// Verify image
			$type = @exif_imagetype($url);
			if (!in_array($type, PhotoFunctions::$validTypes, true)) {
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
			if (!ImportController::photo($tmp_name, $request['albumID'])) {
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

}