<?php

namespace App\Http\Requests\Contracts;

interface HasPhotoIDs
{
	const PHOTO_IDS_ATTRIBUTE = 'photoIDs';

	/**
	 * @return int[]
	 */
	public function photoIDs(): array;
}
