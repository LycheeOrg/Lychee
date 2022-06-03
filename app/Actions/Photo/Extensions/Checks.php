<?php

namespace App\Actions\Photo\Extensions;

use App\Actions\Diagnostics\Checks\BasicPermissionCheck;
use App\Exceptions\InsufficientFilesystemPermissions;
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
 */
trait Checks
{
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
}
