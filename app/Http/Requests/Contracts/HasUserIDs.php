<?php

namespace App\Http\Requests\Contracts;

interface HasUserIDs extends HasAlbumIDs
{
	public const USER_IDS_ATTRIBUTE = 'userIDs';

	/**
	 * @return int[]
	 */
	public function userIDs(): array;
}
