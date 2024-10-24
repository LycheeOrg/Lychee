<?php

namespace App\Contracts\Http\Requests;

interface HasUsername
{
	/**
	 * @return string
	 */
	public function username(): string;
}
