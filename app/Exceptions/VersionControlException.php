<?php

namespace App\Exceptions;

/**
 * VersionControlException.
 */
class VersionControlException extends BaseException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(500, $msg, $previous);
	}
}
