<?php

namespace App\Actions\Import\Extensions;

use App\Exceptions\JsonError;
use App\Models\Logs;
use Helpers;
use Illuminate\Support\Facades\Storage;

trait Checks
{
	public function checkPermissions()
	{
		// Check permissions
		if (Helpers::hasPermissions(Storage::path('import') === false)) {
			Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');

			throw new JsonError('An upload-folder is missing or not readable and writable!');
		}
	}
}
