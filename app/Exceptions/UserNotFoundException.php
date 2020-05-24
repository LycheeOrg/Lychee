<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class UserNotFoundException extends Exception
{
	public function __construct(
		$id = 0,
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Could not find specified user (' . $id . ')', $code, $previous);
	}
}
