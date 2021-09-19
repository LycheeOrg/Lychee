<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * PhotoSkippedException.
 *
 * Returns status code 409 (Conflict) to an HTTP client.
 */
class PhotoSkippedException extends LycheeBaseException
{
	public function __construct(\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_CONFLICT, 'The photo has been skipped', $previous);
	}
}
