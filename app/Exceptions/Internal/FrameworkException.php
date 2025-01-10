<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

/**
 * FrameworkException.
 *
 * This exception is meant to wrap the generic PHP standard exceptions which
 * are thrown by the framework.
 * Unfortunately, some methods of the framework don't throw exceptions which
 * implement a framework-specific interface, but simply use the standard
 * exceptions.
 * Most likely these exceptions also indicate a bug in Lychee, because we
 * (the developers of Lychee) have used the framework in a wrong way.
 * In order to keep our code clean and not throwing generic exceptions,
 * the whole way up the call stack, these generic exceptions should be
 * nicely encapsulated.
 * This also helps debugging the problem, because the exception message
 * includes a method name and a line number.
 */
class FrameworkException extends LycheeLogicException
{
	public function __construct(string $what, ?\Throwable $previous = null)
	{
		parent::__construct($what . ' failed', $previous);
	}
}
