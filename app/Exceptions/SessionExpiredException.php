<?php

namespace App\Exceptions;

class SessionExpiredException extends BaseLycheeException
{
	/** @var int HTTP_PAGE_EXPIRED proprietary Laravel HTTP status code to indicate session expiration */
	public const HTTP_PAGE_EXPIRED = 419;

	public const HTTP_LOGIN_TIMEOUT = 440;

	public const DEFAULT_MESSAGE = 'Session expired';

	public function __construct(int $httpStatusCode = self::HTTP_PAGE_EXPIRED, string $msg = self::DEFAULT_MESSAGE, ?\Throwable $previous = null)
	{
		parent::__construct($httpStatusCode, $msg, $previous);
	}
}
