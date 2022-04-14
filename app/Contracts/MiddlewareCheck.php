<?php

namespace App\Contracts;

interface MiddlewareCheck
{
	/**
	 * @return bool
	 *
	 * @throws InternalLycheeException
	 */
	public function assert(): bool;
}
