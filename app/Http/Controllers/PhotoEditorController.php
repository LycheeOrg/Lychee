<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\Logs;
use App\Photo;
use Illuminate\Http\Request;
use Storage;

class PhotoEditorController extends Controller
{
	public function __construct()
	{
	}

	/**
	 * Given a photoID and a direction (+1: 90° clockwise, -1: 90° counterclockwise) rotate an image.
	 *
	 * @param Request $request
	 *
	 * @return tring
	 */
	public function rotate(Request $request)
	{
		if (!Configs::get_value('editor_enabled', '0')) {
			return 'false';
		}

		$request->validate([
			'photoID' => 'string|required',
			'direction' => 'integer|required',
		]);

		$photo = Photo::find($request['photoID']);
		$direction = $request['direction'];

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');

			return false;
		}

		$img_types = ['big', 'medium', 'medium2x', 'small', 'small2x', 'thumb', 'thumb2x'];
		foreach ($img_types as $img_type) {
			$pathType = strtoupper($img_type);
			if (substr($pathType, 5) !== 'THUMB') {
				$filename = $photo->url;
			} else {
				$filename = $photo->thumbUrl;
			}
			if (($split = strpos($pathType, '2')) !== false) {
				$pathType = substr($pathType, 0, $split);
			}
			$uploadFolder = Storage::path(strtolower($pathType) . '/');

			$img_path = $uploadFolder . $photo->url;

			if (strpos($img_type, '2x') > 0) {
				$filename = preg_replace('/^(.*)\.(.*)$/', '\1@2x.\2', $filename);
			}

			$img_path = $uploadFolder . $filename;
			if (file_exists($img_path)) {
				$image = new \Imagick();
				$image->readImage($img_path);

				if ($direction == 1) {
					$image->rotateImage(new \ImagickPixel(), 90);
				} elseif ($direction == -1) {
					$image->rotateImage(new \ImagickPixel(), -90);
				}

				$image->writeImage();
				$image->clear();
				$image->destroy();
			}
		}

		return 'true';
	}
}
