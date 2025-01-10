<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * MediaFileOperationException.
 *
 * Indicates that a type of format of a media file is unsupported.
 * Returns status code 422 (Unprocessable entity) to an HTTP client.
 */
class MediaFileUnsupportedException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'File format not supported';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $msg, $previous);
	}
}
