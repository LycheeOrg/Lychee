<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class GitNotExecutableException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct(base_path('.git') . ' (and subdirectories) are not executable, check the permissions.', $code, $previous);
	}
}