<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasPhotoID
{
	/**
	 * @return string|null
	 */
	public function photoID(): ?string;
}
