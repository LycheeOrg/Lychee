<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

/**
 * QueryBuilderException.
 *
 * This exception is meant to wrap the generic PHP standard exceptions which
 * are thrown by the Laravel DB and Eloquent Query Builder.
 * Unfortunately, neither Laravel nor Eloquent wrap their exceptions
 * nicely into some "Laravel exception".
 * In order to keep our code clean and not throwing generic exception,
 * we must work around this Laravel flaw.
 */
class QueryBuilderException extends FrameworkException
{
	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct('Laravel/Eloquent query builder', $previous);
	}
}
