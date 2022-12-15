<?php

namespace App\Contracts\Http;

use App\Contracts\Exceptions\InternalLycheeException;

interface MiddlewareCheck
{
	/**
	 * @return bool
	 *
	 * @throws InternalLycheeException
	 */
	public function assert(): bool;
}
