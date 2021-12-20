<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\InternalLycheeException;
use App\Contracts\MiddlewareCheck;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Schema;

class IsInstalled implements MiddlewareCheck
{
	/**
	 * @throws InternalLycheeException
	 */
	public function assert(): bool
	{
		try {
			return
				// this should not happen but you never know.
				// if the key is not provided AND
				//	 the database (config table exists) is set
				//   or installed.log exists
				// this will generate an infinite loop. We do not want that.
				!file_exists(base_path('.NO_SECURE_KEY')) &&
				// base safety
				file_exists(base_path('installed.log')) &&
				// This is the second safety:
				// Assume you do a "git pull" but forget to do the migration,
				// the installed.log will not be created!!!
				Schema::hasTable('configs');
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel framework', $e);
		}
	}
}
