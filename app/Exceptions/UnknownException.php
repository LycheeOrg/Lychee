<?php

namespace App\Exceptions;

class UnknownException extends BaseException
{
	public function __construct(\Throwable $previous = null)
	{
		parent::__construct(500, 'Unknown Lychee exception', $previous);
	}
}
