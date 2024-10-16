<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasPhotoID
{
	/**
	 * @return string|null
	 */
	public function photoID(): ?string;
}
