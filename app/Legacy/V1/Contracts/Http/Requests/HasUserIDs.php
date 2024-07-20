<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasUserIDs
{
	/**
	 * @return int[]
	 */
	public function userIDs(): array;
}
