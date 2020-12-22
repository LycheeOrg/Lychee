<?php

namespace App\Contracts;

interface MiddlewareCheck
{
	public function assert(): bool;
}
