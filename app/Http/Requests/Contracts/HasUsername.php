<?php

namespace App\Http\Requests\Contracts;

interface HasUsername
{
	const USERNAME_ATTRIBUTE = 'username';

	/**
	 * @return string
	 */
	public function username(): string;
}
