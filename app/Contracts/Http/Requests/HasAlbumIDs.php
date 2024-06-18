<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasAlbumIDs
{
	/**
	 * @return string[]
	 */
	public function albumIDs(): array;
}
