<?php

namespace App\Exceptions;

use App\Response;
use Exception;
use Throwable;

class JsonError extends Exception
{
	public function __construct(
		$message,
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}

	public function render()
	{
		return Response::error($this->message);
	}
}
