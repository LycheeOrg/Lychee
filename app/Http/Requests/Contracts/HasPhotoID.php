<?php

namespace App\Http\Requests\Contracts;

interface HasPhotoID
{
	const PHOTO_ID_ATTRIBUTE = 'photoID';

	/**
	 * @return int|null
	 */
	public function photoID(): ?int;
}
