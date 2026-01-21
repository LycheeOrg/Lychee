<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * When trying to delete albums with a corrupted tree, this can only lead to troubles.
 * For this reason we throw this exception and prevent the deletion.
 */
class CorruptedTreeException extends InvalidPropertyException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous, Response::HTTP_CONFLICT);
	}
}