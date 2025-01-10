<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * NoWriteAccessOnLogsExceptions.
 *
 * Indicates any error related to the access rights on the logs.
 * Basically we crash because we couldn't write and that create an infinite loop.
 */
class NoWriteAccessOnLogsExceptions extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INSUFFICIENT_STORAGE, 'Could not write in the logs. Check that storage/logs/ and containing files have proper permissions.', $previous);
	}
}