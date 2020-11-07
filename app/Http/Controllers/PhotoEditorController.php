<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\ModelFunctions\PhotoFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Http\Request;
use Storage;

class PhotoEditorController extends Controller
{
	/**
	 * @var PhotoFuctions
	 */
	private $photoFunctions;

	public function __construct(PhotoFunctions $photoFunctions)
	{
		$this->photoFunctions = $photoFunctions;
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
		// Safety check...
		if (!Configs::get_value('editor_enabled', '0')) {
			return 'false';
		}

		if (!Configs::hasImagick()) {
			// @codeCoverageIgnoreStart
			return 'false';
			// @codeCoverageIgnoreEnd
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

			return 'false';
		}

		if ($this->photoFunctions->isVideo($photo)) {
			Logs::error(__METHOD__, __LINE__, 'Trying to rotate a video');

			return 'false';
		}

		// direction is valid?
		if (($direction != 1) && ($direction != -1)) {
			Logs::error(__METHOD__, __LINE__, 'Direction must be 1 or -1');

			return 'false';
		}

		// Abort on symlinks to avoid messing with originals linked
		if (is_link(Storage::path('big/') . $photo->url)) {
			// @codeCoverageIgnoreStart
			Logs::error(__METHOD__, __LINE__, 'Synlinked images cannot be rotated');

			return 'false';
			// @codeCoverageIgnoreEnd
		}

		// We must rotate all the various formats
		$img_types = ['big', 'medium', 'medium2x', 'small', 'small2x', 'thumb', 'thumb2x'];
		$save_photo = false;
		foreach ($img_types as $img_type) {
			// This will be FALSE if not 2x, or the position of the '2' char otherwise
			$image_2x = strpos($img_type, '2');

			// Build path to stored image
			if (substr($img_type, 0, 5) !== 'thumb') {
				// Rotate image sizes
				$filename = $photo->url;
				if (!is_null($photo->{$img_type})) {
					$x_pos = strpos($photo->{$img_type}, 'x');
					$old_w = substr($photo->{$img_type}, 0, $x_pos);
					$old_h = substr($photo->{$img_type}, $x_pos + 1);
					$photo->{$img_type} = $old_h . 'x' . $old_w;
					$save_photo = true;
				}
			} else {
				$filename = $photo->thumbUrl;
			}
			if ($image_2x !== false) {
				$img_type = substr($img_type, 0, $image_2x);
				$filename = preg_replace('/^(.*)\.(.*)$/', '\1@2x.\2', $filename);
			}
			$uploadFolder = Storage::path($img_type . '/');
			$img_path = $uploadFolder . $photo->url;

			// Rotate the image
			$img_path = $uploadFolder . $filename;
			if (file_exists($img_path)) {
				$image = new \Imagick();
				$image->readImage($img_path);

				if ($direction == 1) {
					$image->rotateImage(new \ImagickPixel(), 90);
				} else {
					$image->rotateImage(new \ImagickPixel(), -90);
				}
				$save_photo = true;
				$image->writeImage();
				$image->clear();
				$image->destroy();
			}
		}
		if ($save_photo) {
			// rotate image width and height and save to the database
			$old_w = $photo->width;
			$old_h = $photo->height;
			$photo->width = $old_h;
			$photo->height = $old_w;
			$photo->save();
		}

		return 'true';
	}
}
