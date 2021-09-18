<?php

namespace App\Actions\Import\Extensions;

use App\Exceptions\InsufficientFilesystemPermissions;
use App\Facades\Helpers;
use App\Models\Logs;
use Illuminate\Support\Facades\Storage;

trait Checks
{
	/**
	 * @throws InsufficientFilesystemPermissions
	 */
	public function checkPermissions()
	{
		if (!Helpers::hasPermissions(Storage::path('import'))) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');

			throw new InsufficientFilesystemPermissions('An upload-folder is missing or not readable and writable!');
		}
	}
}
