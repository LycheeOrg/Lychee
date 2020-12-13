<?php

namespace App\MiddlewareFunctions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class IsInstalled
{
	public function assert(): bool
	{
		// this should not happen but you never know.
		// if the key is not provided AND
		//	 the database (config table exists) is set
		//   or installed.log exists
		// this will generate an infinite loop. We do not want that.
		if (file_exists(base_path('.NO_SECURE_KEY'))) {
			return false;
		}

		// base safety
		if (file_exists(base_path('installed.log'))) {
			return true;
		}

		// This is the second safety:
		// Assume you do a "git pull" but forget to do the migration,
		// the installed.log will not be created!!!
		try {
			if (Schema::hasTable('configs')) {
				return true;
			}

			return false;
		} catch (QueryException $e) {
			return false;
		}
	}
}
