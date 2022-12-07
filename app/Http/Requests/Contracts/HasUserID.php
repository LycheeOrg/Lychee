<?php

namespace App\Http\Requests\Contracts;

interface HasUserID
{
	/**
	 * @return int
	 */
	public function userID(): int;
}
