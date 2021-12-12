<?php

namespace App\Http\Requests\Contracts;

interface HasUsername
{
	public const USERNAME_ATTRIBUTE = 'username';

	/**
	 * @return string
	 */
	public function username(): string;
}
