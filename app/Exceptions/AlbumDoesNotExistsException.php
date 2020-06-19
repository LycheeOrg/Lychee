<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AlbumDoesNotExistsException extends Exception
{
	public function __construct(
		$code = 0,
		Throwable $previous = null
	) {
		parent::__construct('Album does not exist.', $code, $previous);
	}

	public function render($request)
	{
		return 'false';
	}
}
