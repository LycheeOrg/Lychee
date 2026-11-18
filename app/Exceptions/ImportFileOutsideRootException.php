<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * A file queued for import left the import root of its owner
 * between the scan and the job (Feature 115, FR-115-18).
 */
class ImportFileOutsideRootException extends BaseLycheeException
{
	public function __construct(string $path)
	{
		parent::__construct(Response::HTTP_FORBIDDEN, 'File left the import root: ' . $path);
	}
}
