<?php

namespace App\Actions\Import\Extensions;

use App\Exceptions\InsufficientFilesystemPermissions;
use App\Facades\Helpers;
use Illuminate\Support\Facades\Storage;

/**
 * Trait Checks.
 *
 * TODO: This trait should be liquidated.
 * The method {@link Checks::checkPermissions()} is only used in one place.
 */
trait Checks
{
	/**
	 * TODO: Move this method to where it belongs or maybe even nuke it entirely.
	 *
	 * There is a somehow related method
	 * {@link \App\Actions\Photo\Extensions\Checks::checkPermissions()}
	 * which is also only used in a single place.
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function checkPermissions()
	{
		if (!Helpers::hasPermissions(Storage::path('import'))) {
			throw new InsufficientFilesystemPermissions('An upload-folder is missing or not readable and writable!');
		}
	}
}
