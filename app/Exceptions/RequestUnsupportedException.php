<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * RequestUnsupportedException.
 *
 * Indicates that the request is unsupported.
 * Returns status code 501 (Not implemented) to an HTTP client.
 * This mainly affects certain request (such as Album::getArchive) if Lychee
 * uses non-local storage for the media files.
 */
class RequestUnsupportedException extends BaseLycheeException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_IMPLEMENTED, $msg, $previous);
	}
}