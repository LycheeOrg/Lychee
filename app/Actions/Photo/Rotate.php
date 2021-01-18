<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Constants;
use App\Assets\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class Rotate
{
	use Constants;

	private function check(Photo $photo, int $direction): bool
	{
		if ($photo->isVideo()) {
			Logs::error(__METHOD__, __LINE__, 'Trying to rotate a video');

			return false;
		}

		// direction is valid?
		if (($direction != 1) && ($direction != -1)) {
			Logs::error(__METHOD__, __LINE__, 'Direction must be 1 or -1');

			return false;
		}

		if (!Configs::hasImagick()) {
			// @codeCoverageIgnoreStart
			Logs::error(__METHOD__, __LINE__, 'imagick is disabled.');

			return false;
			// @codeCoverageIgnoreEnd
		}

		// Abort on symlinks to avoid messing with originals linked
		if (is_link(Storage::path('big/') . $photo->url)) {
			// @codeCoverageIgnoreStart
			Logs::error(__METHOD__, __LINE__, 'SynLinked images cannot be rotated');

			return false;
			// @codeCoverageIgnoreEnd
		}

		return true;
	}

	public function do(Photo $photo, int $direction)
	{
		if (!$this->check($photo, $direction)) {
			return false;
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
				$filename = Helpers::ex2x($filename);
			}
			$uploadFolder = Storage::path($img_type . '/');

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

		return true;
	}
}
