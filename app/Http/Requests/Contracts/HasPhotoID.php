<?php

namespace App\Http\Requests\Contracts;

interface HasPhotoID
{
	/**
	 * @return string|null
	 */
	public function photoID(): ?string;
}
