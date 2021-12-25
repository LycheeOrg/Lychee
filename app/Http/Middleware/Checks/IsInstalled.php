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
				!file_exists(base_path('.NO_SECURE_KEY')) &&
				Schema::hasTable('configs');
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
