<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * MediaFileOperationException.
 *
 * Indicates that a type of format of a media file is unsupported.
 * Returns status code 422 (Unprocessable entity) to an HTTP client.
 */
class MediaFileUnsupportedException extends LycheeBaseException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $msg, $previous);
	}
}