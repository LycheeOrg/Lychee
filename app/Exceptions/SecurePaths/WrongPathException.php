<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\SecurePaths;

use App\Exceptions\BaseLycheeException;
use Symfony\Component\HttpFoundation\Response;

class WrongPathException extends BaseLycheeException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_FOUND, 'File not found', $previous);
	}
}
