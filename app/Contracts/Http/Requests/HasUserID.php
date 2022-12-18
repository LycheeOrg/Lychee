<?php

namespace App\Contracts\Http\Requests;

interface HasUserID
{
	/**
	 * @return int
	 */
	public function userID(): int;
}
