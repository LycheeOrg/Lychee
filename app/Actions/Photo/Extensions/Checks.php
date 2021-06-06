<?php

namespace App\Actions\Photo\Extensions;

use App\Actions\Diagnostics\Checks\BasicPermissionCheck;
use App\Exceptions\FolderIsNotWritable;
use App\Exceptions\JsonError;
use App\Facades\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

trait Checks
{
	use Constants;

	/**
	 * @throws FolderIsNotWritable
	 */
	public function checkPermissions()
	{
		$errors = [];
		$check = new BasicPermissionCheck();
		$check->folders($errors);
		if (count($errors) > 0) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');
			foreach ($errors as $error) {
				Logs::error(__METHOD__, __LINE__, $error);
			}
			throw new FolderIsNotWritable();
		}
	}

	public function folderPermission($folder)
	{
		$path = Storage::path($folder);

		if (Helpers::hasPermissions($path) === false) {
			Logs::notice(__METHOD__, __LINE__, 'Skipped extaction of video from live photo, because ' . $path . ' is missing or not readable and writable.');
			throw new FolderIsNotWritable();
		}

		return $path;
	}

	/**
	 * Check if a picture has a duplicate
	 * We compare the checksum to the other Photos or LivePhotos.
	 *
	 * @return false|Photo
	 */
	public function get_duplicate($checksum, $photoID = null)
	{
		return Photo::where(function ($q) use ($checksum) {
			$q->where('checksum', '=', $checksum)
				->orWhere('livePhotoChecksum', '=', $checksum);
		})->where('id', '<>', $photoID)->first();
	}

	/**
	 * Returns 'photo' if it is a photo
	 * Returns 'video' if it is a video
	 * Returns 'raw' if it is an accepted file (we only check extensions).
	 *
	 * @throws 'error message' if it is something else
	 *
	 * @param $file
	 * @param $extension
	 *
	 * @return string
	 */
	public function file_type($file, string $extension)
	{
		// check raw files
		$raw_formats = strtolower(Configs::get_value('raw_formats', ''));
		if (in_array(strtolower($extension), explode('|', $raw_formats), true)) {
			return 'raw';
		}

		if (in_array(strtolower($extension), $this->validExtensions, true)) {
			$mimeType = $file['type'];
			if (in_array($mimeType, $this->validVideoTypes, true)) {
				return 'video';
			}

			return 'photo';
		}

		// let's check for the mimetype
		// maybe we don't have a photo
		if (!function_exists('exif_imagetype')) {
			Logs::error(__METHOD__, __LINE__, 'EXIF library not loaded. Make sure exif is enabled in php.ini');
			throw new JsonError('EXIF library not loaded on the server!');
		}

		$type = @exif_imagetype($file['tmp_name']);
		if (in_array($type, $this->validTypes, true)) {
			return 'photo';
		}

		Logs::error(__METHOD__, __LINE__, 'Photo type not supported: ' . $file['name']);
		throw new JsonError('Photo type not supported!');
	}
}
