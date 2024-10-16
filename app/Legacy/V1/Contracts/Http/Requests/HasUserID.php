<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasUserID
{
	/**
	 * @return int
	 */
	public function userID(): int;
}
