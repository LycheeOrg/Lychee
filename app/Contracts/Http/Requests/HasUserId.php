<?php

namespace App\Contracts\Http\Requests;

interface HasUserId
{
	/**
	 * @return int
	 */
	public function userId(): int;
}
