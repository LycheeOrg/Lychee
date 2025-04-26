<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnexpectedContentType extends BaseLycheeException
{
	public function __construct(string $content_type, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_ACCEPTABLE, 'Content type unacceptable. Content type "' . $content_type . '" required', $previous);
	}
}
