<?php

namespace App\Exceptions;

use Throwable;

class PhotoSkippedException extends JsonWarning
{
	public function __construct(
		$message,
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}
}
