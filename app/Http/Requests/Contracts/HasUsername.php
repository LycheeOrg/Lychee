<?php

namespace App\Http\Requests\Contracts;

interface HasUsername
{
	/**
	 * @return string
	 */
	public function username(): string;
}
