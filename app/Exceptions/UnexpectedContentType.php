<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnexpectedContentType extends BaseLycheeException
{
	public function __construct(string $contentType, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_ACCEPTABLE, 'Content type unacceptable. Content type "' . $contentType . '" required', $previous);
	}
}
