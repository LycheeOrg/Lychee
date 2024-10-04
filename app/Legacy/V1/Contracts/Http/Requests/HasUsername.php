<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasUsername
{
	/**
	 * @return string
	 */
	public function username(): string;
}
