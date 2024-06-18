<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * MediaFileMissingException.
 *
 * Indicates that a type of format of a media file is unsupported.
 * Returns status code 404 (Not found) to an HTTP client.
 */
class MediaFileMissingException extends BaseLycheeException
{
	public const DEFAULT_MESSAGE = 'The media file is missing';

	public function __construct(string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_NOT_FOUND, $msg, $previous);
	}
}