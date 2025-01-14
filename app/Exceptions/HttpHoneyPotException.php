<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Exception thrown when attacker hits the HoneyPot.
 */
class HttpHoneyPotException extends HttpException
{
	/**
	 * Basic constructor.
	 *
	 * @param string          $path     used by the attacker
	 * @param \Throwable|null $previous exception
	 *
	 * @return void
	 */
	public function __construct(string $path, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_I_AM_A_TEAPOT, sprintf('The route %s could not be found.', $path), $previous);
	}
}