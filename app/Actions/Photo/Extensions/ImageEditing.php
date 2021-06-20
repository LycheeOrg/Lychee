<?php

namespace App\Actions\Photo\Extensions;

use App\Facades\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Trait ImageEditing.
 *
 * @deprecated
 */
trait ImageEditing
{
	/**
	 * @param Photo $photo
	 * @param string Path of the video frame
	 *
	 * @return void
	 */
	public function createSmallerImages(Photo $photo, string $frame_tmp = '')
	{
		if ($frame_tmp === '' || $photo->isRaw()) {
			// Create medium file for normal photos and for raws
			$mediumMaxWidth = intval(Configs::get_value('medium_max_width'));
			$mediumMaxHeight = intval(Configs::get_value('medium_max_height'));
			$this->resizePhoto($photo, 'medium', $mediumMaxWidth, $mediumMaxHeight, $frame_tmp);

			if (Configs::get_value('medium_2x') === '1') {
				$this->resizePhoto($photo, 'medium2x', $mediumMaxWidth * 2, $mediumMaxHeight * 2, $frame_tmp);
			}
		}

		$smallMaxWidth = intval(Configs::get_value('small_max_width'));
		$smallMaxHeight = intval(Configs::get_value('small_max_height'));
		$this->resizePhoto($photo, 'small', $smallMaxWidth, $smallMaxHeight, $frame_tmp);

		if (Configs::get_value('small_2x') === '1') {
			$this->resizePhoto($photo, 'small2x', $smallMaxWidth * 2, $smallMaxHeight * 2, $frame_tmp);
		}
	}

	/**
	 * @param Photo $photo
	 *
	 * @return string Path of the jpg file
	 */
	public function createJpgFromRaw(Photo $photo): string
	{
		// we need imagick to do the job
		if (!Configs::hasImagick()) {
			Logs::notice(__METHOD__, __LINE__, 'Saving JPG of raw file failed: Imagick not installed.');

			return '';
		}

		$fullPath = $photo->full_path;
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);

		// test if Imagick supports the filetype
		// Query return file extensions as all upper case
		if (!in_array(strtoupper($ext), \Imagick::queryformats())) {
			Logs::notice(__METHOD__, __LINE__, 'Filetype ' . $ext . ' not supported by Imagick.');

			return '';
		}

		if (!($tmp_file = tempnam(sys_get_temp_dir(), 'lychee')) ||
			!rename($tmp_file, $tmp_file . '.jpeg')) {
			Logs::notice(__METHOD__, __LINE__, 'Could not create a temporary file.');

			return '';
		}
		$tmp_file .= '.jpeg';
		Logs::notice(__METHOD__, __LINE__, 'Saving JPG of raw file to ' . $tmp_file);

		$resWidth = $resHeight = 0;
		$width = $photo->width;
		$height = $photo->height;

		try {
			$this->imageHandler->scale($fullPath, $tmp_file, $width, $height, $resWidth, $resHeight);
		} catch (Exception $e) {
			Logs::error(__METHOD__, __LINE__, 'Failed to create JPG from raw file ' . $fullPath);

			return '';
		}

		return $tmp_file;
	}

	/**
	 * Creates smaller copies of Photo.
	 *
	 * @param Photo  $photo
	 * @param string $type
	 * @param int    $maxWidth
	 * @param int    $maxHeight
	 * @param string Path of the video frame
	 *
	 * @return bool
	 */
	public function resizePhoto(Photo $photo, string $type, int $maxWidth, int $maxHeight, string $frame_tmp = ''): bool
	{
		$width = $photo->width;
		$height = $photo->height;

		if ($frame_tmp === '') {
			$filename = $photo->filename;
			$url = Storage::path('big/' . $filename);
		} else {
			$filename = $photo->thumb_filename;
			$url = $frame_tmp;
		}

		// Both image sizes of the same type are stored in the same folder
		// ie: medium and medium2x both belong in LYCHEE_UPLOADS_MEDIUM
		$pathType = strtoupper($type);
		if (($split = strpos($pathType, '2')) !== false) {
			$pathType = substr($pathType, 0, $split);
		}

		$uploadFolder = Storage::path(strtolower($pathType) . '/');
		if (Helpers::hasPermissions($uploadFolder) === false) {
			Logs::notice(__METHOD__, __LINE__, 'Skipped creation of ' . $type . '-photo, because ' . $uploadFolder . ' is missing or not readable and writable.');

			return false;
		}

		// Add the @2x postfix if we're dealing with an HiDPI type
		if (strpos($type, '2x') > 0) {
			$filename = Helpers::ex2x($filename);
		}

		// Is photo big enough?
		if (($width <= $maxWidth || $maxWidth == 0) && ($height <= $maxHeight || $maxHeight == 0)) {
			Logs::notice(__METHOD__, __LINE__, 'No resize (image is too small: ' . $maxWidth . 'x' . $maxHeight . ')!');

			return false;
		}

		$resWidth = $resHeight = 0;
		if (!$this->imageHandler->scale($url, $uploadFolder . $filename, $maxWidth, $maxHeight, $resWidth, $resHeight)) {
			Logs::error(__METHOD__, __LINE__, 'Failed to ' . $type . ' resize image');

			return false;
		}

		$photo->{$type . '_width'} = $resWidth;
		$photo->{$type . '_height'} = $resHeight;

		return true;
	}

	/**
	 * Create thumbnail for a picture.
	 *
	 * @param Photo $photo
	 * @param string Path of the video frame
	 *
	 * @return bool returns true when successful
	 */
	public function createThumb(Photo $photo, string $frame_tmp = ''): bool
	{
		Logs::notice(__METHOD__, __LINE__, 'Photo filename is ' . $photo->filename);

		$src = ($frame_tmp === '') ? Storage::path('big/' . $photo->filename) : $frame_tmp;
		$photoName = explode('.', $photo->filename);

		$this->imageHandler->crop($src, Storage::path('thumb/' . $photoName[0] . '.jpeg'), SizeVariant::THUMBNAIL_DIM, SizeVariant::THUMBNAIL_DIM);

		if (Configs::get_value('thumb_2x') === '1' && $photo->width >= SizeVariant::THUMBNAIL2X_DIM && $photo->height >= SizeVariant::THUMBNAIL2X_DIM) {
			// Retina thumbs
			$this->imageHandler->crop($src, Storage::path('thumb/' . $photoName[0] . '@2x.jpeg'), SizeVariant::THUMBNAIL2X_DIM, SizeVariant::THUMBNAIL2X_DIM);
			$photo->thumb2x = true;
		} else {
			$photo->thumb2x = false;
		}

		return true;
	}
}
