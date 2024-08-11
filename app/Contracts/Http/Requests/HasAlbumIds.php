<?php

namespace App\Contracts\Http\Requests;

interface HasAlbumIds
{
	/**
	 * @return string[]
	 */
	public function albumIds(): array;
}
