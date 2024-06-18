<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasUserID
{
	/**
	 * @return int
	 */
	public function userID(): int;
}
