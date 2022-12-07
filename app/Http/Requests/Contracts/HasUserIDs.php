<?php

namespace App\Http\Requests\Contracts;

interface HasUserIDs
{
	/**
	 * @return int[]
	 */
	public function userIDs(): array;
}
