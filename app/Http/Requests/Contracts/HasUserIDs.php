<?php

namespace App\Http\Requests\Contracts;

interface HasUserIDs
{
	public const USER_IDS_ATTRIBUTE = 'userIDs';

	/**
	 * @return int[]
	 */
	public function userIDs(): array;
}
