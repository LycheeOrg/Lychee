<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\InternalLycheeException;
use App\Contracts\MiddlewareCheck;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
		} catch (QueryException $e) {
			return !Str::contains($e->getMessage(), 'SQLSTATE[HY000] [1045]');
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
