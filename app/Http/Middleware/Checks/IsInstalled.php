<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware\Checks;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Http\MiddlewareCheck;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsInstalled implements MiddlewareCheck
{
	/**
	 * @throws InternalLycheeException
	 *
	 * @codeCoverageIgnore
	 */
	public function assert(): bool
	{
		try {
			return
				config('app.key') !== null &&
				config('app.key') !== '' &&
				Schema::hasTable('configs');
		} catch (QueryException $e) {
			// Authentication to DB failed.
			// This means that we cannot even check that `configs` is present,
			// therefore we will just assume it is not.
			//
			// This can only happen if:
			// - Connection with DB is broken (firewall?)
			// - Connection with DB is not set (MySql without credentials)
			//
			// We only check Authentication to DB failed and just skip in
			// the other cases to get a proper message error.
			if (Str::contains($e->getMessage(), 'SQLSTATE[HY000] [1045]')) {
				return false;
			}
			// Not coverable by tests unless we actually remove the php dependencies...
			if (Str::contains($e->getMessage(), 'could not find driver')) {
				return false;
			}
			throw $e;
		} catch (BindingResolutionException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}