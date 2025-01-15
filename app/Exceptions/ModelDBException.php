<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * ModelDBException.
 *
 * Indicates any error related to models and DB.
 * This includes error like failed saves, updates, fetches but also problems
 * with queries.
 * The message should at least indicate which model caused the problem and
 * some broad description of what failed, e.g. "unable to update model xyz".
 * Returns status code 500 (Internal server error) to an HTTP client.
 * As this exception reports a 5xx code (opposed to a 4xx code) this
 * exception indicates a server-side error.
 * This means the failing operation is typically expected not to fail and
 * the client or user cannot do anything about it.
 * Sometimes a model operation may fail (for example an SQL INSERT), because
 * a value which has been passed to the query is syntactically invalid.
 * However, throwing this exception in those cases is a bug in this application.
 * Input values must be validated before and raise a proper exception (with
 * a 4xx error code) if they appear to be invalid.
 */
class ModelDBException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}

	/**
	 * Creates a new instance with template message.
	 *
	 * The message of the new instance is
	 * > Could not $operationName $modelName
	 *
	 * @param string          $modelName     the name of the model
	 * @param string          $operationName the failed operation in gerund
	 *                                       form, typically "creating",
	 *                                       "updating", "deleting", ...
	 * @param \Throwable|null $previous      an optional previous exception
	 *
	 * @return ModelDBException
	 */
	public static function create(string $modelName, string $operationName, ?\Throwable $previous = null): ModelDBException
	{
		return new ModelDBException(Str::ucfirst($operationName) . ' ' . $modelName . ' failed', $previous);
	}
}