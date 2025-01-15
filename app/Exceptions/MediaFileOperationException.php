<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

/**
 * MediaFileOperationException.
 *
 * Indicates any error related to media files.
 * This includes error like moving/copying media files, etc.
 * Returns status code 500 (Internal server error) to an HTTP client.
 *
 * As this exception reports a 5xx code (opposed to a 4xx code) this
 * exception indicates a server-side error.
 * This means the failing operation is typically expected not to fail and
 * the client or user cannot do anything about it.
 *
 * Sometimes an operation may fail (for example cropping an image), because
 * the media type or format is unsupported.
 * However, throwing this exception in those cases would constitute a bug in
 * this application.
 * The type and format of a media file should be validated first and the
 * application should throw an {@link MediaFileUnsupportedException} instead.
 */
class MediaFileOperationException extends FileOperationException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous);
	}
}
