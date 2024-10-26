<?php

namespace App\Contracts\Http\Requests;

use App\Enum\PhotoLayoutType;

interface HasPhotoLayout
{
	/**
	 * @return PhotoLayoutType|null
	 */
	public function photoLayout(): ?PhotoLayoutType;
}
