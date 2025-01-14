<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use App\Contracts\Exceptions\ExternalLycheeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The abstract base class for all Lychee exceptions.
 *
 * Any exception which is thrown by the outer-most methods of the Lychee
 * application should extend this class.
 * Outer-most methods means those methods which are entry (and also exit)
 * points of the Lychee application for the framework.
 * These include at least the methods of the HTTP controllers and the methods
 * of the console commands.
 *
 * Note, that messages of exceptions inherited from this class are presented
 * to the (end) user: either via the CLI or a browser.
 * Hence, the derived exceptions and messages should sufficiently hide
 * internal implementation details, but still be helpful for an ordinary user.
 * Moreover, due to security considerations, internal information (like paths,
 * values of variables, control flows) should not be exposed.
 * However, those internal details might be chained into the previous
 * exception.
 *
 * The framework ensures that only the message of this exception is
 * printed if the application runs in production mode.
 * If the application runs in debug mode, a full backtrace of the chained
 * exceptions is printed.
 *
 * Extending {@link HttpException} allows sending back a proper HTTP status
 * codes to a web-client.
 * The status code has no meaning in CLI mode.
 * Also, the framework ensures that the web-client either receives a proper
 * JSON response or a rendered HTML page, depending on the client's request
 * (see {@link \Illuminate\Foundation\Exceptions\Handler::render()} and
 * subsequent calls to
 * {@link \Illuminate\Foundation\Exceptions\Handler::prepareJsonResponse()}
 * or
 * {@link \Illuminate\Foundation\Exceptions\Handler::prepareResponse()}).
 */
abstract class BaseLycheeException extends HttpException implements ExternalLycheeException
{
	/**
	 * Constructor.
	 *
	 * @param int             $httpStatusCode the HTTP status code reported to
	 *                                        the web-client
	 * @param string          $message        the message (should be useful
	 *                                        for a typical end-user)
	 * @param \Throwable|null $previous       an optional previous exception
	 */
	protected function __construct(int $httpStatusCode, string $message, ?\Throwable $previous = null)
	{
		$code = null;
		if ($previous !== null) {
			// Some Throwable will throw strings instead of int: SQLite when failing on read only DB for example.
			$code = is_int($previous->getCode()) ? $previous->getCode() : 0;
		}
		parent::__construct($httpStatusCode, $message, $previous, [], $code ?? 0);
	}
}
