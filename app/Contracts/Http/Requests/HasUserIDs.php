<?php

namespace App\Contracts\Http\Requests;

interface HasUserIDs
{
	/**
	 * @return int[]
	 */
	public function userIDs(): array;
}
