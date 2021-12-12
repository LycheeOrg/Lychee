<?php

namespace App\Http\Requests\Contracts;

interface HasPhotoID
{
	public const PHOTO_ID_ATTRIBUTE = 'photoID';

	/**
	 * @return string|null
	 */
	public function photoID(): ?string;
}
