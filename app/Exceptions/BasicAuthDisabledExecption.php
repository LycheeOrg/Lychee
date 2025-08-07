<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * BasicAuthDisabledExecption.
 *
 * Returns status code 403 (Forbidden) to an HTTP client.
 */
class BasicAuthDisabledExecption extends BaseLycheeException
{
	public function __construct()
	{
		parent::__construct(Response::HTTP_FORBIDDEN, 'Basic Auth is disabled by configuration', null);
	}
}
