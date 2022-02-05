<?php

namespace App\Actions\Photo\Extensions;

use App\Actions\Diagnostics\Checks\BasicPermissionCheck;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\MediaFileUnsupportedException;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;

trait Checks
{
	use Constants;

	/**
	 * @throws InsufficientFilesystemPermissions
	 */
	public function checkPermissions(): void
	{
		$errors = [];
		$check = new BasicPermissionCheck();
		$check->folders($errors);
		if (count($errors) > 0) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');
			foreach ($errors as $error) {
				Logs::error(__METHOD__, __LINE__, $error);
			}
			throw new InsufficientFilesystemPermissions('An upload-folder is missing or not readable and writable');
		}
	}

	/**
	 * Check if a picture has a duplicate
	 * We compare the checksum to the other Photos or LivePhotos.
	 *
	 * @param string $checksum
	 *
	 * @return ?Photo
	 */
	public function get_duplicate(string $checksum): ?Photo
	{
		/** @var Photo|null $photo */
		$photo = Photo::query()
			->where('checksum', '=', $checksum)
			->orWhere('original_checksum', '=', $checksum)
			->orWhere('live_photo_checksum', '=', $checksum)
			->first();

		return $photo;
	}

	/**
	 * Returns the kind of media file.
	 *
	 * The kind is one out of:
	 *
	 *  - `'photo'` if the media file is a photo
	 *  - `'video'` if the media file is a video
	 *  - `'raw'` if the media file is an accepted file, but none of the other
	 *    two kinds (we only check extensions).
	 *
	 * @param SourceFileInfo $sourceFileInfo information about source file
	 *
	 * @return string either `'photo'`, `'video'` or `'raw'`
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws ExternalComponentMissingException
	 */
	public function file_kind(SourceFileInfo $sourceFileInfo): string
	{
		$extension = $sourceFileInfo->getOriginalExtension();
		// check raw files
		$raw_formats = strtolower(Configs::get_value('raw_formats', ''));
		if (in_array(strtolower($extension), explode('|', $raw_formats), true)) {
			return 'raw';
		}

		if (in_array(strtolower($extension), $this->validExtensions, true)) {
			$mimeType = $sourceFileInfo->getOriginalMimeType();
			if (in_array($mimeType, $this->validVideoTypes, true)) {
				return 'video';
			}

			return 'photo';
		}

		// let's check for the mimetype
		// maybe we don't have a photo
		if (!function_exists('exif_imagetype')) {
			Logs::error(__METHOD__, __LINE__, 'EXIF library not loaded. Make sure exif is enabled in php.ini');
			throw new ExternalComponentMissingException('EXIF library not loaded on the server!');
		}

		$type = exif_imagetype($sourceFileInfo->getFile()->getAbsolutePath());
		if (in_array($type, $this->validTypes, true)) {
			return 'photo';
		}

		Logs::error(__METHOD__, __LINE__, 'Photo type not supported: ' . $sourceFileInfo->getOriginalFilename());
		throw new MediaFileUnsupportedException('Photo type not supported!');
	}
}
