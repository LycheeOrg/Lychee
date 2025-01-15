<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ConflictingPropertyException.
 *
 * Special case of an {@link InvalidPropertyException}.
 * A model could not be processed, because one of its properties conflicts
 * with a property of another model.
 * Most likely, a unique constraint is not met.
 * The error message should at least indicate the type of model and the name
 * of the property.
 * Returns status code 409 (Conflict) to an HTTP client.
 * As this exception reports a 4xx status code (opposed to a 5xx code),
 * this exception should be used if the client or user is "responsible" of
 * the error in some sense, i.e. if the invalid value has been provided by the
 * client.
 * This also includes corner cases where the user is only involved very
 * indirectly, e.g. if the user uploads a photo with broken MIME data.
 */
class ConflictingPropertyException extends InvalidPropertyException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, $previous, Response::HTTP_CONFLICT);
	}
}