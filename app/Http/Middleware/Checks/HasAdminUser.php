<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Http\MiddlewareCheck;
use App\Exceptions\Internal\FrameworkException;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Schema;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HasAdminUser implements MiddlewareCheck
{
	/**
	 * @throws InternalLycheeException
	 */
	public function assert(): bool
	{
		// This is necessary in the case that someone is using the web-updater.
		if (Schema::hasColumn('users', 'may_administrate')) {
			try {
				return User::query()->where('may_administrate', '=', true)->count() > 0;
			} catch (BindingResolutionException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
				throw new FrameworkException('Laravel\'s container component', $e);
			}
		} else {
			return true;
		}
	}
}