<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class FileOperationException.
 *
 * Indicates any error related to files.
 * This includes error like moving/copying files, reading files, etc.
 * Returns status code 500 (Internal server error) to an HTTP client.
 *
 * If the file is a media file (i.e. image or video) consider using the
 * more specific {@link MediaFileOperationException} instead.
 *
 * Returns status code 500 (Internal server error) to an HTTP client.
 *
 * As this exception reports a 5xx code (opposed to a 4xx code) this
 * exception indicates a server-side error.
 * This means the failing operation is typically expected not to fail and
 * the client or user cannot do anything about it.
 */
class FileOperationException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
