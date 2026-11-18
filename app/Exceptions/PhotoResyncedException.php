<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

	/**
	 * @param string          $message
	 * @param \Throwable|null $previous
	 * @param string|null     $photo_id the existing photo the upload duplicates (Feature 116)
	 */
	public function __construct(
		string $message = self::DEFAULT_MESSAGE,
		?\Throwable $previous = null,
		?string $photo_id = null,
	) {
		parent::__construct($message, $previous, $photo_id);
	}
}
