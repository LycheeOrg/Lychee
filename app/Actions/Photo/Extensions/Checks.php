<?php

namespace App\Actions\Photo\Extensions;

use App\Actions\Diagnostics\Checks\BasicPermissionCheck;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\MediaFile;
use App\Models\Configs;
use App\Models\Photo;

/**
 * Trait Checks.
 *
 * TODO: This trait should be liquidated.
 * It is a random collection of methods without an inner relationship.
 * In particular
 *
 *  - {@link Checks::checkPermissions()} is only used in a single place
 *  - {@link Checks::get_duplicate()} is only used in a single place
 *  - {@link Checks::file_kind()} should be part of a class which combines
 *    all methods which deal with MIME type; maybe e.g.
 *    {@link MediaFile}
 */
trait Checks
{
	use Constants;

	/**
	 * TODO: Move this method to where it belongs or maybe even nuke it entirely.
	 *
	 * There is a somehow related method
	 * {@link \App\Actions\Import\Extensions\Checks::checkPermissions()}
	 * which is also only used in a single place.
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function checkPermissions(): void
	{
		$errors = [];
		$check = new BasicPermissionCheck();
		$check->folders($errors);
		if (count($errors) > 0) {
			throw new InsufficientFilesystemPermissions('An upload-folder is missing or not readable and writable');
		}
	}

	/**
	 * Check if a picture has a duplicate
	 * We compare the checksum to the other Photos or LivePhotos.
	 *
	 * TODO: Move this method to where it belongs.
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
	 * TODO: Move this method to where it belongs and consolidate this logic with the MIME-related logic of the remaining application.
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
			throw new ExternalComponentMissingException('EXIF library mssing.');
		}

		$type = exif_imagetype($sourceFileInfo->getFile()->getAbsolutePath());
		if (in_array($type, $this->validTypes, true)) {
			return 'photo';
		}

		throw new MediaFileUnsupportedException('Photo type not supported: ' . $sourceFileInfo->getOriginalName());
	}
}
