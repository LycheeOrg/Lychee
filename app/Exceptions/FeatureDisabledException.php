<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class FeatureDisabledException extends BaseLycheeException
{
	public function __construct(string $feature)
	{
		parent::__construct(Response::HTTP_NOT_IMPLEMENTED, sprintf("Feature '%s' is disabled", $feature), null);
	}
}
