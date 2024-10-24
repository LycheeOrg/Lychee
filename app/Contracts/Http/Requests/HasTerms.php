<?php

namespace App\Contracts\Http\Requests;

interface HasTerms
{
	/**
	 * @return string[]
	 */
	public function terms(): array;
}
