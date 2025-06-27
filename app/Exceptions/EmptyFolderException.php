<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class EmptyFolderException.
 *
 * Indicates that an operation was attempted on an empty folder.
 * Returns status code 412 to an HTTP client.
 *
 * This exception is typically thrown when a user attempts to perform an
 * operation that requires the folder to contain items, but the folder is empty.
 */
class EmptyFolderException extends BaseLycheeException
{
	/**
	 * @param string $path
	 */
	public function __construct(string $path)
	{
		parent::__construct(Response::HTTP_PRECONDITION_FAILED, sprintf('%s is empty', $path), null);
	}
}