<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * GalleryPasswordRequiredException.
 *
 * Thrown when an anonymous visitor requests gallery content while a gallery
 * password is set and the visitor has not unlocked the gallery yet.
 */
class GalleryPasswordRequiredException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'Gallery password required';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNAUTHORIZED, $msg, $previous);
	}
}
