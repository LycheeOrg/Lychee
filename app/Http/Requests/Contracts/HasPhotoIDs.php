<?php

namespace App\Http\Requests\Contracts;

interface HasPhotoIDs
{
	public const PHOTO_IDS_ATTRIBUTE = 'photoIDs';

	/**
	 * @return string[]
	 */
	public function photoIDs(): array;
}
