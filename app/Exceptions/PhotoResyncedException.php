<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

/**
 * PhotoReSyncedException.
 *
 * Returns status code 409 (Conflict) to an HTTP client.
 */
class PhotoResyncedException extends PhotoSkippedException
{
	public const DEFAULT_MESSAGE = 'The photo has been skipped, but its metadata has been updated';

	public function __construct(string $message = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct($message, $previous);
	}
}
