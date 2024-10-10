<?php

namespace App\Contracts\Http\Requests;

interface HasUserIds
{
	/**
	 * @return int[]
	 */
	public function userIds(): array;
}
