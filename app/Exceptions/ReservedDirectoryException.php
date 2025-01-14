<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

/**
 * Class ReservedDirectoryException.
 *
 * Indicates that the front-end provided a string which should point to a
 * directory outside Lychee, but the directory is actually part of Lychee.
 * Returns status code 409 (Conflict) to an HTTP client.
 *
 * The back-end was used to report the string `'Given path is reserved'`
 * to the front-end before this exception was created.
 */
class ReservedDirectoryException extends ConflictingPropertyException
{
	public const DEFAULT_MESSAGE = 'The given path is a reserved path of Lychee';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous);
	}
}
