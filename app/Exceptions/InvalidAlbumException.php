<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * InvalidAlbumException.
 *
 * Indicates that the provided album does not match the conditions required.
 * This is for example when we expect an Album but we get a Smart Album or a Tag Al bum
 */
class InvalidAlbumException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null, int $status_code = Response::HTTP_INTERNAL_SERVER_ERROR)
	{
		parent::__construct($status_code, $msg, $previous);
	}
}
