<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\HoneyPot;

use App\Exceptions\HttpHoneyPotException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Basic definition of a Pipe.
 */
abstract class BasePipe
{
	/**
	 * Handle the current path.
	 *
	 * @param string                        $path called by the query
	 * @param \Closure(string $path): never $next
	 *
	 * @return never
	 *
	 * @throws HttpHoneyPotException
	 * @throws NotFoundHttpException
	 */
	// @codeCoverageIgnoreStart
	abstract public function handle(string $path, \Closure $next): never;
	// @codeCoverageIgnoreEnd

	/**
	 * using abort(404) does not give the info which path was called.
	 * This could be very useful when debugging.
	 * By throwing a proper exception we preserve this info.
	 * This will generate a log line of type ERROR.
	 *
	 * @param string $path called
	 *
	 * @return never
	 *
	 * @throws NotFoundHttpException
	 */
	final protected function throwNotFound(string $path): never
	{
		throw new NotFoundHttpException(sprintf('The route %s could not be found.', $path));
	}

	/**
	 * Similar to abort(404), abort(418) does not give info.
	 * It is more interesting to raise a proper exception.
	 * By having a proper exception we are also able to decrease the severity to NOTICE.
	 *
	 * @param string $path called
	 *
	 * @return never
	 *
	 * @throws HttpHoneyPotException
	 */
	final protected function throwTeaPot(string $path): never
	{
		throw new HttpHoneyPotException($path);
	}
}
