<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasAlbumID
{
	/**
	 * @return string|null
	 */
	public function albumID(): ?string;
}
