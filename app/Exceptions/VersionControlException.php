<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * VersionControlException.
 *
 * Returns status code 500 (Internal Server Error) to an HTTP client.
 */
class VersionControlException extends BaseLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
