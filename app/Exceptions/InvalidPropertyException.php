<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * InvalidPropertyException.
 *
 * Indicates that a model could not be processed, because one of its
 * properties had an invalid value.
 * The error message should at least indicate the type of model and the name
 * of the property.
 * Returns status code 422 (Unprocessable entity) to an HTTP client.
 * As this exception reports a 4xx status code (opposed to a 5xx code),
 * this exception should be used if the client or user is "responsible" for
 * the error in some sense, i.e. if the invalid value has been provided by the
 * client.
 * This also includes corner cases where the user is only involved very
 * indirectly, e.g. if the user uploads a photo with broken MIME data.
 */
class InvalidPropertyException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null, int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY)
	{
		parent::__construct($statusCode, $msg, $previous);
	}
}
