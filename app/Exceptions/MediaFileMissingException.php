<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * MediaFileMissingException.
 *
 * Indicates that a type of format of a media file is unsupported.
 * Returns status code 404 (Not found) to an HTTP client.
 */
class MediaFileMissingException extends LycheeBaseException
{
	public function __construct(string $msg = 'The media file is missing', \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_FOUND, $msg, $previous);
	}
}