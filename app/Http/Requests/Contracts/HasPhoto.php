<?php

namespace App\Http\Requests\Contracts;

use App\Models\Photo;

interface HasPhoto
{
	public const PHOTO_ID_ATTRIBUTE = 'photoID';

	/**
	 * @return Photo|null
	 */
	public function photo(): ?Photo;
}
