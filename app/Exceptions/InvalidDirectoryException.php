<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

/**
 * Class InvalidDirectoryException.
 *
 * Indicates that the front-end provided a string which should point to a
 * valid directory but doesn't.
 * Returns status code 422 (Unprocessable entity) to an HTTP client.
 *
 * The back-end was used to report the string `'Given path is not a directory'`
 * to the front-end before this exception was created.
 */
class InvalidDirectoryException extends InvalidPropertyException
{
	public const DEFAULT_MESSAGE = 'Given path is not a directory';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous);
	}
}
